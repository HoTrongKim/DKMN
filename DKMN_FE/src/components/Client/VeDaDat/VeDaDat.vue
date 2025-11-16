<template>
  <div class="ticket-page container py-4">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <div class="card shadow-sm ticket-card">
          <div class="card-header d-flex align-items-center justify-content-between ticket-card__header">
            <div>
              <p class="ticket-card__eyebrow mb-1">Thông tin vé</p>
              <h5 class="mb-0">Vé đã đặt</h5>
            </div>
            <span class="badge bg-success ticket-status" v-if="currentTicket">Thành công</span>
          </div>

          <div class="card-body ticket-card__body">
            <div v-if="isLoading" class="text-center py-4">
              <div class="spinner-border text-primary mb-3" role="status"></div>
              <div>Đang tải vé của bạn...</div>
            </div>

            <div v-else-if="serverError" class="alert alert-danger mb-0">
              {{ serverError }}
            </div>

            <div v-else-if="!currentTicket" class="text-center py-4">
              <div class="mb-2">Chưa có vé nào được lưu.</div>
              <router-link class="btn btn-primary" to="/TrangChu">Đặt vé ngay</router-link>
            </div>

            <div v-else>
              <div class="mb-4" v-if="hasMultipleTickets">
                <label class="form-label fw-semibold">Chọn vé</label>
                <select class="form-select" v-model.number="activeTicketIndex">
                  <option v-for="(item, index) in tickets" :key="index" :value="index">
                    {{ item.from || '-' }} → {{ item.to || '-' }} · {{ formatTime(item.createdAt) }}
                  </option>
                </select>
              </div>
              <ul class="list-group list-group-flush ticket-info-list mb-4">
                <li class="list-group-item ticket-info-item" v-if="currentTicket.from || currentTicket.to">
                  <span class="ticket-info-label">Hành trình</span>
                  <span class="ticket-info-value text-muted">{{ currentTicket.from || '-' }} → {{ currentTicket.to || '-' }}</span>
                </li>
                <li class="list-group-item ticket-info-item" v-if="currentTicket.date">
                  <span class="ticket-info-label">Ngày đi</span>
                  <span class="ticket-info-value text-muted">{{ currentTicket.date }}</span>
                </li>
                <li class="list-group-item ticket-info-item" v-if="currentTicket.company">
                  <span class="ticket-info-label">Nhà vận hành</span>
                  <span class="ticket-info-value text-muted">{{ currentTicket.company }}</span>
                </li>
                <li class="list-group-item ticket-info-item ticket-info-item--stacked" v-if="currentTicket.pickupStation || currentTicket.dropoffStation">
                  <div class="ticket-info-row">
                    <span class="ticket-info-label">Điểm đón</span>
                    <span class="ticket-info-value text-muted">{{ currentTicket.pickupStation || '-' }}</span>
                  </div>
                  <div class="ticket-info-row">
                    <span class="ticket-info-label">Điểm trả</span>
                    <span class="ticket-info-value text-muted">{{ currentTicket.dropoffStation || '-' }}</span>
                  </div>
                </li>
                <li class="list-group-item ticket-info-item" v-if="currentTicket.passengers">
                  <span class="ticket-info-label">Số hành khách</span>
                  <span class="ticket-info-value text-muted">{{ currentTicket.passengers }}</span>
                </li>
                <li class="list-group-item ticket-info-item">
                  <span class="ticket-info-label">Mã giao dịch</span>
                  <span class="ticket-info-value">{{ currentTicket.paymentId }}</span>
                </li>
                <li class="list-group-item ticket-info-item">
                  <span class="ticket-info-label">Cổng thanh toán</span>
                  <span class="ticket-info-value text-uppercase">{{ currentTicket.gateway }}</span>
                </li>
                <li class="list-group-item ticket-info-item" v-if="currentTicket.tripId">
                  <span class="ticket-info-label">Mã chuyến</span>
                  <span class="ticket-info-value">{{ currentTicket.tripId }}</span>
                </li>
                <li class="list-group-item ticket-info-item" v-if="(currentTicket.seats || []).length">
                  <span class="ticket-info-label">Ghế</span>
                  <span class="ticket-info-value text-muted">{{ currentTicket.seats.join(', ') }}</span>
                </li>
                <li class="list-group-item ticket-info-item ticket-info-item--emphasis">
                  <span class="ticket-info-label">Thành tiền</span>
                  <span class="ticket-info-value">{{ formatCurrency(currentTicket.total) }}</span>
                </li>
                <li class="list-group-item ticket-info-item">
                  <span class="ticket-info-label">Ngày giờ</span>
                  <span class="ticket-info-value">{{ formatTime(currentTicket.createdAt) }}</span>
                </li>
              </ul>

              <div class="ticket-actions d-flex flex-wrap gap-2">
                <router-link to="/TrangChu" class="btn btn-outline-secondary">Về trang chủ</router-link>
                <button class="btn btn-danger" @click="clearTicket()">Xóa vé này</button>
                <button class="btn btn-primary" :disabled="!canRate" v-if="currentTicket?.tripId" @click="goRate">
                  Đánh giá
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div
      v-if="notice.visible"
      class="alert alert-warning position-fixed bottom-0 start-0 m-3 shadow-sm d-flex align-items-center gap-2"
      role="alert"
    >
      <i class="bx bx-info-circle fs-5"></i>
      <span>{{ notice.text }}</span>
      <button type="button" class="btn-close ms-auto" @click="notice.visible = false"></button>
    </div>
  </div>
</template>

<script>
import api from '../../../services/api'

const LEGACY_TICKET_KEY = 'dkmn:lastTicket'
const TICKET_STORE_KEY = 'dkmn:tickets'
const USER_INFO_KEY = 'userInfo'

export default {
  name: 'VeDaDat',
  data() {
    return {
      tickets: [],
      activeTicketIndex: 0,
      ratingsIndex: {},
      notice: { visible: false, text: '' },
      isLoading: false,
      serverError: '',
    }
  },
  computed: {
    currentTicket() {
      if (!this.tickets.length) return null
      return this.tickets[this.activeTicketIndex] || this.tickets[0] || null
    },
    canRate() {
      if (!this.currentTicket || !this.currentTicket.tripId) return false
      const alreadyRated = !!this.ratingsIndex[String(this.currentTicket.tripId)]
      if (alreadyRated) return false
      const status = String(this.currentTicket.tripStatus || '').toLowerCase()
      if (!status) return true
      const allowed = ['completed', 'hoan_tat', 'da_di', 'finished', 'done', 'success']
      return allowed.includes(status)
    },
    hasMultipleTickets() {
      return this.tickets.length > 1
    },
  },
  methods: {
    formatCurrency(value) {
      return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value || 0)
    },
    formatTime(ts) {
      try {
        const d = new Date(ts)
        return d.toLocaleString('vi-VN')
      } catch (e) {
        return ''
      }
    },
    clearTicket(index = this.activeTicketIndex) {
      const currentList = [...this.tickets]
      if (index >= 0 && index < currentList.length) {
        currentList.splice(index, 1)
        this.tickets = currentList
        if (this.activeTicketIndex >= this.tickets.length) {
          this.activeTicketIndex = Math.max(0, this.tickets.length - 1)
        }
        this.updateTicketStore(currentList)
      }
    },
    async loadRatingsIndex() {
      try {
        const response = await api.get('/ratings/me')
        const list = response?.data?.data || []
        const idx = {}
        list.forEach((item) => {
          if (item?.tripId) {
            idx[String(item.tripId)] = true
          }
        })
        this.ratingsIndex = idx
        try {
          localStorage.setItem('dkmn:ratings', JSON.stringify(list))
        } catch (error) {
          console.warn('cannot persist ratings cache', error)
        }
      } catch (error) {
        try {
          const fallback = localStorage.getItem('dkmn:ratings')
          const arr = fallback ? JSON.parse(fallback) : []
          const idx = {}
          arr.forEach((r) => {
            if (r?.tripId) {
              idx[String(r.tripId)] = true
            }
          })
          this.ratingsIndex = idx
        } catch (e) {
          this.ratingsIndex = {}
        }
      }
    },
    goRate() {
      if (!this.ticket?.tripId) return
      if (!this.canRate) {
        this.notice.text = 'Chuyến đi chưa đủ điều kiện để đánh giá.'
        this.notice.visible = true
        return
      }
      this.$router.push({
        path: '/client-danh-gia',
        query: {
          tripId: String(this.ticket.tripId || ''),
          from: this.ticket.from || '',
          to: this.ticket.to || '',
          date: this.ticket.date || '',
        },
      })
    },
    getCurrentUserInfo() {
      try {
        const raw = localStorage.getItem(USER_INFO_KEY)
        return raw ? JSON.parse(raw) : null
      } catch (e) {
        return null
      }
    },
    getTicketOwnerKey() {
      const info = this.getCurrentUserInfo()
      if (!info || typeof info !== 'object') return null
      const id = info.id || info.user_id || info.ma || info.ma_nguoi_dung || info.code || info.slug
      if (id) return 'id:' + id
      const email = info.email || info.username || info.ten_dang_nhap
      if (email) return 'email:' + String(email).toLowerCase()
      const phone = info.so_dien_thoai || info.phone || info.dien_thoai
      if (phone) return 'phone:' + String(phone)
      return null
    },
    readTicketStore() {
      try {
        const raw = localStorage.getItem(TICKET_STORE_KEY)
        if (!raw) return {}
        const parsed = JSON.parse(raw)
        return parsed && typeof parsed === 'object' && !Array.isArray(parsed) ? parsed : {}
      } catch (e) {
        return {}
      }
    },
    writeTicketStore(store) {
      try {
        localStorage.setItem(TICKET_STORE_KEY, JSON.stringify(store || {}))
      } catch (error) {
        console.warn('cannot persist ticket store', error)
      }
    },
    loadTicketsFromStore() {
      const ownerKey = this.getTicketOwnerKey()
      if (!ownerKey) return []
      const store = this.readTicketStore()
      const existing = store[ownerKey]
      if (Array.isArray(existing)) return existing
      if (existing) return [existing]
      return []
    },
    saveTicketForOwner(ticket) {
      if (!ticket) return
      const ownerKey = this.getTicketOwnerKey()
      if (!ownerKey) return
      const store = this.readTicketStore()
      const normalizedTicket = this.normalizeTicket(ticket)
      const existing = Array.isArray(store[ownerKey]) ? store[ownerKey] : []
      const alreadyExists = existing.some((item) => JSON.stringify(item) === JSON.stringify(normalizedTicket))
      if (!alreadyExists) {
        existing.unshift(normalizedTicket)
      }
      store[ownerKey] = existing.slice(0, 10)
      this.writeTicketStore(store)
      try {
        localStorage.setItem(LEGACY_TICKET_KEY, JSON.stringify(store[ownerKey][0] || null))
      } catch (error) {
        console.warn('cannot update legacy ticket cache', error)
      }
    },
    clearStoredTicket() {
      try {
        localStorage.removeItem(LEGACY_TICKET_KEY)
      } catch (error) {
        console.warn('cannot remove legacy ticket', error)
      }
      const ownerKey = this.getTicketOwnerKey()
      if (!ownerKey) return
      const store = this.readTicketStore()
      if (store[ownerKey]) {
        delete store[ownerKey]
        this.writeTicketStore(store)
      }
    },
    loadLegacyTicket() {
      try {
        const raw = localStorage.getItem(LEGACY_TICKET_KEY)
        const parsed = raw ? JSON.parse(raw) : null
        if (!parsed) return []
        return Array.isArray(parsed) ? parsed : [parsed]
      } catch (error) {
        return []
      }
    },
    ticketFromQuery() {
      const q = this.$route?.query || {}
      if (!q || (!q.paymentId && !q.total)) {
        return null
      }
      return {
        paymentId: String(q.paymentId || ''),
        gateway: String(q.gateway || ''),
        total: Number(q.total || 0),
        tripId: q.tripId ? String(q.tripId) : null,
        seats: q.seats
          ? String(q.seats)
              .split(',')
              .map((s) => s.trim())
              .filter(Boolean)
          : [],
        createdAt: q.createdAt ? Number(q.createdAt) : Date.now(),
        from: q.from ? String(q.from) : '',
        to: q.to ? String(q.to) : '',
        date: q.date ? String(q.date) : '',
        passengers: q.passengers ? String(q.passengers) : '',
        pickupStation: q.pickupStation ? String(q.pickupStation) : '',
        dropoffStation: q.dropoffStation ? String(q.dropoffStation) : '',
        company: q.company ? String(q.company) : '',
        tripStatus: q.tripStatus || 'pending',
      }
    },
  },
  async mounted() {
    this.isLoading = true
    try {
      const queryTicket = this.ticketFromQuery()
      if (queryTicket) {
        this.saveTicketForOwner(queryTicket)
        this.tickets = this.loadTicketsFromStore()
      } else {
        const hasOwner = !!this.getTicketOwnerKey()
        if (hasOwner) {
          this.tickets = this.loadTicketsFromStore()
        } else {
          this.tickets = this.loadLegacyTicket()
        }
      }
    } catch (error) {
      this.serverError = 'Không thể tải vé đã đặt. Vui lòng thử lại.'
    } finally {
      this.isLoading = false
    }
    await this.loadRatingsIndex()
  },
}
</script>

<style scoped>
.ticket-page {
  min-height: calc(100vh - 100px);
  background: radial-gradient(circle at 10% -20%, #e0f2fe, transparent 40%), #f4f6fb;
}

.ticket-card {
  border: none;
  border-radius: 24px;
  overflow: hidden;
  position: relative;
  box-shadow: 0 25px 60px rgba(15, 23, 42, 0.08);
}

.ticket-card::after {
  content: '';
  position: absolute;
  top: -45px;
  right: -45px;
  width: 160px;
  height: 160px;
  background: radial-gradient(circle, rgba(59, 130, 246, 0.25), transparent 70%);
  pointer-events: none;
}

.ticket-card__header {
  border-bottom: none;
  padding: 1.75rem 2rem;
  background: linear-gradient(120deg, #0ea5e9, #2563eb);
  color: #fff;
}

.ticket-card__header h5 {
  color: inherit;
  font-weight: 700;
}

.ticket-card__eyebrow {
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.12em;
  opacity: 0.85;
}

.ticket-card__body {
  padding: 2rem;
  background: linear-gradient(180deg, #ffffff 0%, #f8f9ff 100%);
}

.ticket-status {
  font-weight: 600;
  padding: 0.5rem 1rem;
  border-radius: 999px;
  box-shadow: 0 10px 30px rgba(15, 118, 110, 0.2);
}

.ticket-info-list {
  border-radius: 18px;
  border: 1px solid #edf0f6;
  overflow: hidden;
  background: rgba(255, 255, 255, 0.95);
}

.ticket-info-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  padding: 1rem 1.5rem;
  border-color: #edf0f6;
  background: transparent;
}

.ticket-info-item--stacked {
  flex-direction: column;
  align-items: stretch;
}

.ticket-info-row {
  display: flex;
  justify-content: space-between;
  gap: 1rem;
}

.ticket-info-row + .ticket-info-row {
  margin-top: 0.5rem;
  padding-top: 0.5rem;
  border-top: 1px dashed #e4e7ec;
}

.ticket-info-label {
  font-weight: 600;
  color: #475467;
}

.ticket-info-value {
  font-weight: 600;
  color: #0f172a;
}

.ticket-info-value.text-muted {
  color: #98a2b3;
}

.ticket-info-item--emphasis {
  background: #f5f8ff;
}

.ticket-info-item--emphasis .ticket-info-value {
  font-size: 1.2rem;
  color: #0b57d0;
}

.ticket-actions .btn {
  min-width: 140px;
  border-radius: 12px;
  font-weight: 600;
  padding: 0.75rem 1.5rem;
  box-shadow: 0 12px 25px rgba(15, 23, 42, 0.08);
}

.ticket-actions .btn:disabled {
  opacity: 0.75;
  box-shadow: none;
}

@media (max-width: 575.98px) {
  .ticket-card__header,
  .ticket-card__body {
    padding: 1.5rem;
  }

  .ticket-info-item {
    flex-direction: column;
    align-items: flex-start;
  }

  .ticket-info-value {
    font-size: 1rem;
  }

  .ticket-actions .btn {
    width: 100%;
  }
}

.alert {
  animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
