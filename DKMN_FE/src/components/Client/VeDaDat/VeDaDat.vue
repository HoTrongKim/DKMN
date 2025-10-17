/<template>
  <div class="container py-4">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <div class="card shadow-sm">
          <div class="card-header bg-white d-flex align-items-center justify-content-between">
            <h5 class="mb-0">Vé đã đặt</h5>
            <span class="badge bg-success" v-if="ticket">Thành công</span>
          </div>
          <div class="card-body">
            <div v-if="!ticket" class="text-center py-4">
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
                <li class="list-group-item d-flex justify-content-between"><span>Mã giao dịch</span><strong>{{ ticket.paymentId }}</strong></li>
                <li class="list-group-item d-flex justify-content-between"><span>Cổng thanh toán</span><strong class="text-uppercase">{{ ticket.gateway }}</strong></li>
                <li class="list-group-item d-flex justify-content-between" v-if="ticket.tripId"><span>Mã chuyến</span><strong>{{ ticket.tripId }}</strong></li>
                <li class="list-group-item" v-if="(ticket.seats||[]).length">
                  <div class="d-flex justify-content-between">
                    <span>Ghế</span>
                    <span class="text-muted">{{ ticket.seats.join(', ') }}</span>
                  </div>
                </li>
                <li class="list-group-item d-flex justify-content-between"><span>Thành tiền</span><strong>{{ formatCurrency(ticket.total) }}</strong></li>
                <li class="list-group-item d-flex justify-content-between"><span>Ngày giờ</span><strong>{{ formatTime(ticket.createdAt) }}</strong></li>
              </ul>

              <div class="d-flex gap-2">
                <router-link to="/TrangChu" class="btn btn-outline-secondary">Về trang chủ</router-link>
                <button class="btn btn-danger" @click="clearTicket">Xóa vé đã lưu</button>
                <router-link
                  v-if="canRate"
                  :to="{ path: '/client-danh-gia' }"
                  class="btn btn-primary"
                >Đánh giá chuyến đi</router-link>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'VeDaDat',
  data() {
    return {
      ticket: null
    }
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
      try { localStorage.removeItem('dkmn:lastTicket') } catch (e) {}
      this.ticket = null
    }
  },
  computed: {
    canRate() {
      if (!this.ticket) return false
      const created = Number(this.ticket.createdAt || 0)
      const oneMinute = 60 * 1000
      return !!this.ticket.tripId && Date.now() - created > oneMinute
    }
  },
  mounted() {
    // Accept inline ticket via route query (fallback to storage)
    const q = this.$route?.query || {}
    if (q && (q.paymentId || q.total)) {
      const inline = {
        paymentId: String(q.paymentId || ''),
        gateway: String(q.gateway || ''),
        total: Number(q.total || 0),
        tripId: q.tripId ? String(q.tripId) : null,
        seats: q.seats ? String(q.seats).split(',').map(s => s.trim()).filter(Boolean) : [],
        createdAt: q.createdAt ? Number(q.createdAt) : Date.now(),
        from: q.from ? String(q.from) : '',
        to: q.to ? String(q.to) : '',
        date: q.date ? String(q.date) : '',
        passengers: q.passengers ? String(q.passengers) : '',
        pickupStation: q.pickupStation ? String(q.pickupStation) : '',
        dropoffStation: q.dropoffStation ? String(q.dropoffStation) : '',
        company: q.company ? String(q.company) : ''
      }
      this.ticket = inline
      return
    }
    try {
      const raw = localStorage.getItem('dkmn:lastTicket')
      if (raw) this.ticket = JSON.parse(raw)
    } catch (e) {
      this.ticket = null
    }
  }
}
</script>

<style>

</style>

