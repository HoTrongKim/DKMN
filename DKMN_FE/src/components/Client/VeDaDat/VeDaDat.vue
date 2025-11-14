<template>
  <div class="container py-4">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <div class="card shadow-sm">
          <div class="card-header bg-white d-flex align-items-center justify-content-between">
            <h5 class="mb-0">Vé đã đặt</h5>
            <span class="badge bg-success" v-if="ticket">Thành công</span>
          </div>

          <div class="card-body">
            <div v-if="isLoading" class="text-center py-4">
              <div class="spinner-border text-primary mb-3" role="status"></div>
              <div>Đang tải vé của bạn...</div>
            </div>

            <div v-else-if="serverError" class="alert alert-danger mb-0">
              {{ serverError }}
            </div>

            <div v-else-if="!ticket" class="text-center py-4">
              <div class="mb-2">Chưa có vé nào được lưu.</div>
              <router-link class="btn btn-primary" to="/TrangChu">Đặt vé ngay</router-link>
            </div>

            <div v-else>
              <ul class="list-group list-group-flush mb-3">
                <li class="list-group-item" v-if="ticket.from || ticket.to">
                  <div class="d-flex justify-content-between">
                    <span>Hành trình</span>
                    <span class="text-muted">{{ ticket.from || '-' }} → {{ ticket.to || '-' }}</span>
                  </div>
                </li>
                <li class="list-group-item" v-if="ticket.date">
                  <div class="d-flex justify-content-between">
                    <span>Ngày đi</span>
                    <span class="text-muted">{{ ticket.date }}</span>
                  </div>
                </li>
                <li class="list-group-item" v-if="ticket.company">
                  <div class="d-flex justify-content-between">
                    <span>Nhà vận hành</span>
                    <span class="text-muted">{{ ticket.company }}</span>
                  </div>
                </li>
                <li class="list-group-item" v-if="ticket.pickupStation || ticket.dropoffStation">
                  <div class="d-flex justify-content-between">
                    <span>Điểm đón</span>
                    <span class="text-muted">{{ ticket.pickupStation || '-' }}</span>
                  </div>
                  <div class="d-flex justify-content-between mt-1">
                    <span>Điểm trả</span>
                    <span class="text-muted">{{ ticket.dropoffStation || '-' }}</span>
                  </div>
                </li>
                <li class="list-group-item" v-if="ticket.passengers">
                  <div class="d-flex justify-content-between">
                    <span>Số hành khách</span>
                    <span class="text-muted">{{ ticket.passengers }}</span>
                  </div>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                  <span>Mã giao dịch</span>
                  <strong>{{ ticket.paymentId }}</strong>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                  <span>Cổng thanh toán</span>
                  <strong class="text-uppercase">{{ ticket.gateway }}</strong>
                </li>
                <li class="list-group-item d-flex justify-content-between" v-if="ticket.tripId">
                  <span>Mã chuyến</span>
                  <strong>{{ ticket.tripId }}</strong>
                </li>
                <li class="list-group-item" v-if="(ticket.seats || []).length">
                  <div class="d-flex justify-content-between">
                    <span>Ghế</span>
                    <span class="text-muted">{{ ticket.seats.join(', ') }}</span>
                  </div>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                  <span>Thành tiền</span>
                  <strong>{{ formatCurrency(ticket.total) }}</strong>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                  <span>Ngày giờ</span>
                  <strong>{{ formatTime(ticket.createdAt) }}</strong>
                </li>
              </ul>

              <div class="d-flex gap-2">
                <router-link to="/TrangChu" class="btn btn-outline-secondary">Về trang chủ</router-link>
                <button class="btn btn-danger" @click="clearTicket">Xóa vé đã lưu</button>
                <button class="btn btn-primary" :disabled="!canRate" v-if="ticket?.tripId" @click="goRate">
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
      ticket: null,
      ratingsIndex: {},
      notice: { visible: false, text: '' },
      isLoading: false,
      serverError: '',
    }
  },
  computed: {
    canRate() {
      if (!this.ticket || !this.ticket.tripId) return false
      const alreadyRated = !!this.ratingsIndex[String(this.ticket.tripId)]
      if (alreadyRated) return false
      const status = String(this.ticket.tripStatus || '').toLowerCase()
      if (!status) return true
      const allowed = ['completed', 'hoan_tat', 'da_di', 'finished', 'done', 'success']
      return allowed.includes(status)
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
    clearTicket() {
      this.clearStoredTicket()
      this.ticket = null
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
    loadTicketFromStore() {
      const ownerKey = this.getTicketOwnerKey()
      if (!ownerKey) return null
      const store = this.readTicketStore()
      return store[ownerKey] || null
    },
    saveTicketForOwner(ticket) {
      if (!ticket) return
      const ownerKey = this.getTicketOwnerKey()
      if (!ownerKey) return
      const store = this.readTicketStore()
      store[ownerKey] = ticket
      this.writeTicketStore(store)
      try {
        localStorage.setItem(LEGACY_TICKET_KEY, JSON.stringify(ticket))
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
        return raw ? JSON.parse(raw) : null
      } catch (error) {
        return null
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
        this.ticket = queryTicket
        this.saveTicketForOwner(queryTicket)
      } else {
        const hasOwner = !!this.getTicketOwnerKey()
        const storedTicket = hasOwner ? this.loadTicketFromStore() : null
        const legacyTicket = hasOwner ? null : this.loadLegacyTicket()
        this.ticket = storedTicket || legacyTicket || null
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

<style>
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
