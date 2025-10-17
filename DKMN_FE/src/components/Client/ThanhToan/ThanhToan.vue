/<template>
  <div class="container py-4">
    <div class="row g-4">
      <div class="col-lg-8">
        <div class="card shadow-sm h-100">
          <div class="card-header bg-white d-flex align-items-center justify-content-between">
            <h5 class="mb-0">Phương thức thanh toán</h5>
            <span class="badge bg-primary">Bảo mật</span>
          </div>
          <div class="card-body">
            <div class="mb-3">
              <label class="form-label fw-semibold">Chọn ví/cổng thanh toán</label>
              <div class="row g-3">
                <div class="col-md-6">
                  <div class="form-check border rounded p-3 h-100">
                    <input class="form-check-input" type="radio" name="gateway" id="gw-momo" value="momo" v-model="selectedGateway">
                    <label class="form-check-label d-block" for="gw-momo">
                      <div class="d-flex justify-content-between align-items-center">
                        <span>MoMo</span>
                        <span class="text-muted small">Phí ~ {{ (fees.momo * 100).toFixed(2) }}%</span>
                      </div>
                    </label>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-check border rounded p-3 h-100">
                    <input class="form-check-input" type="radio" name="gateway" id="gw-card" value="card" v-model="selectedGateway">
                    <label class="form-check-label d-block" for="gw-card">
                      <div class="d-flex justify-content-between align-items-center">
                        <span>Thẻ ngân hàng (Visa/Master/JCB)</span>
                        <span class="text-muted small">Phí ~ {{ (fees.card * 100).toFixed(2) }}%</span>
                      </div>
                    </label>
                  </div>
                </div>
                
              </div>
            </div>

            <!-- Card form -->
            <div v-if="selectedGateway === 'card'" class="mb-3">
              <div class="row g-3">
                <div class="col-12">
                  <label class="form-label">Số thẻ</label>
                  <input type="text" class="form-control" inputmode="numeric" autocomplete="cc-number" placeholder="1234 5678 9012 3456" v-model.trim="cardForm.number" @input="formatCardNumber" :disabled="status.isProcessing">
                </div>
                <div class="col-12">
                  <label class="form-label">Tên chủ thẻ</label>
                  <input type="text" class="form-control" autocomplete="cc-name" placeholder="NGUYEN VAN A" v-model.trim="cardForm.name" :disabled="status.isProcessing">
                </div>
                <div class="col-6">
                  <label class="form-label">Hết hạn (MM/YY)</label>
                  <input type="text" class="form-control" inputmode="numeric" autocomplete="cc-exp" placeholder="MM/YY" v-model.trim="cardForm.expiry" @input="formatExpiry" :disabled="status.isProcessing">
                </div>
                <div class="col-6">
                  <label class="form-label">CVV</label>
                  <input type="password" class="form-control" inputmode="numeric" autocomplete="cc-csc" maxlength="4" placeholder="***" v-model.trim="cardForm.cvv" :disabled="status.isProcessing">
                </div>
              </div>
              <div v-if="selectedGateway === 'card' && !cardFormValid" class="form-text text-danger mt-1">Vui lòng nhập thông tin thẻ hợp lệ.</div>
            </div>

            <div class="mb-3">
              <label class="form-label fw-semibold">Mã khuyến mãi</label>
              <div class="input-group">
                <input type="text" class="form-control" placeholder="Nhập mã (vd: GIAM10, FREESHIP)" v-model.trim="promoCode" :disabled="status.isProcessing">
                <button class="btn btn-outline-primary" @click="applyPromo" :disabled="!promoCode || status.isProcessing">Áp dụng</button>
              </div>
              <div v-if="promoMessage" class="form-text" :class="{ 'text-success': promoApplied, 'text-danger': !promoApplied }">{{ promoMessage }}</div>
            </div>

            <div class="d-flex gap-2">
              <button class="btn btn-primary" @click="startPayment" :disabled="!canPay">
                <i class="bx bx-credit-card me-1"></i> Thanh toán ngay
              </button>
              <button class="btn btn-outline-secondary" @click="resetSelection" :disabled="status.isProcessing">Làm mới</button>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-4">
        <div class="card shadow-sm">
          <div class="card-header bg-white">
            <h6 class="mb-0">Tóm tắt đơn hàng</h6>
          </div>
          <div class="card-body">
            <ul class="list-group list-group-flush mb-3">
              <li class="list-group-item">
                <div class="d-flex justify-content-between">
                  <span class="fw-semibold">Hành trình</span>
                  <span class="text-muted" v-if="fromCity && toCity">{{ fromCity }} → {{ toCity }}</span>
                </div>
                <div class="d-flex justify-content-between mt-1">
                  <span class="fw-semibold">Ngày đi</span>
                  <span class="text-muted" v-if="travelDate">{{ travelDate }}</span>
                </div>
              </li>
              <li class="list-group-item" v-if="company">
                <div class="d-flex justify-content-between">
                  <span class="fw-semibold">Nhà vận hành</span>
                  <span class="text-muted">{{ company }}</span>
                </div>
              </li>
              <li class="list-group-item" v-if="pickupStation || dropoffStation">
                <div class="d-flex justify-content-between">
                  <span class="fw-semibold">Điểm đón</span>
                  <span class="text-muted">{{ pickupStation || '-' }}</span>
                </div>
                <div class="d-flex justify-content-between mt-1">
                  <span class="fw-semibold">Điểm trả</span>
                  <span class="text-muted">{{ dropoffStation || '-' }}</span>
                </div>
              </li>
              <li class="list-group-item" v-if="passengers">
                <div class="d-flex justify-content-between">
                  <span class="fw-semibold">Số hành khách</span>
                  <span class="text-muted">{{ passengers }}</span>
                </div>
              </li>
              <li class="list-group-item d-flex justify-content-between align-items-center">
                <span>Tạm tính</span>
                <strong>{{ formatCurrency(subtotal) }}</strong>
              </li>
              <li v-if="selectedSeats.length" class="list-group-item">
                <div class="d-flex justify-content-between">
                  <span>Ghế đã chọn</span>
                  <span class="text-muted">{{ selectedSeats.join(', ') }}</span>
                </div>
              </li>
              <li class="list-group-item d-flex justify-content-between align-items-center">
                <span>Phí cổng thanh toán</span>
                <strong>{{ formatCurrency(paymentFee) }}</strong>
              </li>
              <li class="list-group-item d-flex justify-content-between align-items-center" v-if="discountAmount > 0">
                <span>Khuyến mãi</span>
                <strong class="text-success">-{{ formatCurrency(discountAmount) }}</strong>
              </li>
              <li class="list-group-item d-flex justify-content-between align-items-center">
                <span>Tổng thanh toán</span>
                <strong class="fs-5">{{ formatCurrency(total) }}</strong>
              </li>
            </ul>

            <div>
              <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="fw-semibold">Trạng thái</span>
                <span :class="statusBadgeClass">{{ status.label }}</span>
              </div>
              <div class="progress" role="progressbar" :aria-valuenow="status.progress" aria-valuemin="0" aria-valuemax="100">
                <div class="progress-bar progress-bar-striped" :class="{ 'progress-bar-animated': status.isProcessing }" :style="{ width: status.progress + '%' }"></div>
              </div>
              <div v-if="status.detail" class="small text-muted mt-2">{{ status.detail }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'ThanhToan',
  data() {
    return {
      // In a real app, these would come from cart/booking state or API
      subtotal: 0, // VND; will load from route if available
      selectedGateway: 'momo',
      promoCode: '',
      promoApplied: false,
      promoMessage: '',
      appliedDiscount: { type: null, value: 0 },
      selectedSeats: [],
      tripId: null,
      // ticket summary fields from query
      fromCity: '',
      toCity: '',
      travelDate: '',
      passengers: '',
      pickupStation: '',
      dropoffStation: '',
      company: '',
      cardForm: {
        number: '',
        name: '',
        expiry: '',
        cvv: ''
      },
      // percentage fees for simple estimation
      fees: {
        momo: 0.011,      // ~1.1%
        card: 0.015       // ~1.5% (ví dụ)
      },
      // status state for real-time updates
      status: {
        code: 'idle', // idle | creating | pending | success | failed | cancelled
        label: 'Chưa thanh toán',
        detail: '',
        progress: 0,
        isProcessing: false
      },
      pollTimer: null,
      mockPaymentId: null
    }
  },
  computed: {
    paymentFee() {
      if (!this.selectedGateway) return 0
      const rate = this.fees[this.selectedGateway] || 0
      return Math.round(this.subtotal * rate)
    },
    cardFormValid() {
      if (this.selectedGateway !== 'card') return true
      const sanitized = this.cardForm.number.replace(/\s+/g, '')
      const isNumberOk = /^\d{16}$/.test(sanitized)
      const isNameOk = this.cardForm.name.length >= 3
      const isExpiryOk = /^(0[1-9]|1[0-2])\/\d{2}$/.test(this.cardForm.expiry)
      const isCvvOk = /^\d{3,4}$/.test(this.cardForm.cvv)
      return isNumberOk && isNameOk && isExpiryOk && isCvvOk
    },
    discountAmount() {
      if (!this.appliedDiscount.type) return 0
      if (this.appliedDiscount.type === 'percent') {
        return Math.round((this.subtotal + this.paymentFee) * this.appliedDiscount.value)
      }
      if (this.appliedDiscount.type === 'flat') {
        return Math.min(this.appliedDiscount.value, this.subtotal + this.paymentFee)
      }
      return 0
    },
    total() {
      const raw = this.subtotal + this.paymentFee - this.discountAmount
      return raw > 0 ? raw : 0
    },
    canPay() {
      const cardOk = this.selectedGateway !== 'card' || this.cardFormValid
      return !!this.selectedGateway && !this.status.isProcessing && this.total > 0 && cardOk
    },

    statusBadgeClass() {
      switch (this.status.code) {
        case 'success':
          return 'badge bg-success'
        case 'failed':
        case 'cancelled':
          return 'badge bg-danger'
        case 'pending':
        case 'creating':
          return 'badge bg-warning text-dark'
        default:
          return 'badge bg-secondary'
      }
    }
  },
  methods: {
    formatCurrency(value) {
      return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value || 0)
    },
    formatCardNumber() {
      const digits = this.cardForm.number.replace(/\D+/g, '').slice(0, 16)
      this.cardForm.number = digits.replace(/(\d{4})(?=\d)/g, '$1 ').trim()
    },
    formatExpiry() {
      const digits = this.cardForm.expiry.replace(/\D+/g, '').slice(0, 4)
      if (digits.length <= 2) {
        this.cardForm.expiry = digits
      } else {
        this.cardForm.expiry = digits.slice(0, 2) + '/' + digits.slice(2)
      }
    },
    resetSelection() {
      if (this.status.isProcessing) return
      this.selectedGateway = 'momo'
      this.promoCode = ''
      this.promoApplied = false
      this.promoMessage = ''
      this.appliedDiscount = { type: null, value: 0 }
      this.setStatus('idle', 'Chưa thanh toán', '', 0, false)
    },
    applyPromo() {
      if (!this.promoCode) return
      const code = this.promoCode.toUpperCase()
      // Demo promo logic
      if (code === 'GIAM10') {
        this.appliedDiscount = { type: 'percent', value: 0.1 }
        this.promoApplied = true
        this.promoMessage = 'Áp dụng giảm 10% thành công.'
      } else if (code === 'FREESHIP') {
        this.appliedDiscount = { type: 'flat', value: 20000 }
        this.promoApplied = true
        this.promoMessage = 'Áp dụng giảm 20.000đ phí thành công.'
      } else {
        this.appliedDiscount = { type: null, value: 0 }
        this.promoApplied = false
        this.promoMessage = 'Mã không hợp lệ hoặc đã hết hạn.'
      }
    },
    setStatus(code, label, detail, progress, isProcessing) {
      this.status = { code, label, detail, progress, isProcessing }
    },
    async startPayment() {
      if (!this.canPay) return
      // Step 1: Create payment (mock)
      this.setStatus('creating', 'Đang khởi tạo', 'Tạo yêu cầu thanh toán...', 10, true)
      try {
        // Replace this with your backend endpoint
        // const { data } = await axios.post('/api/payments/create', { total: this.total, gateway: this.selectedGateway, meta: { promo: this.appliedDiscount } })
        // For demo, simulate API
        const data = await this.mockCreatePayment()
        this.mockPaymentId = data.id
        if (this.selectedGateway === 'card') {
          this.setStatus('pending', 'Đang xác thực thẻ', 'Đang xác thực thông tin thẻ...', 40, true)
          await this.mockCardAuthorize(this.cardForm)
          this.setStatus('pending', 'Đang trừ tiền', 'Đang thực hiện giao dịch...', 60, true)
          await this.mockCardCapture()
        } else {
          this.setStatus('pending', 'Đang chờ thanh toán', 'Mở cổng thanh toán...', 40, true)
          // Step 2: Redirect/open gateway (mock) - MoMo only
          await this.mockOpenGateway(this.selectedGateway)
          this.setStatus('pending', 'Đang xử lý', 'Vui lòng xác nhận trong ứng dụng MoMo.', 55, true)
        }

        // Step 3: Poll status
        this.beginPollingStatus()
      } catch (e) {
        this.setStatus('failed', 'Thất bại', 'Không thể khởi tạo thanh toán. Vui lòng thử lại.', 0, false)
      }
    },
    beginPollingStatus() {
      this.clearPolling()
      // Simulate status updates every 1.2s
      this.pollTimer = setInterval(async () => {
        try {
          const result = await this.mockQueryStatus(this.mockPaymentId)
          if (result.status === 'success') {
            this.setStatus('success', 'Thành công', 'Thanh toán đã được xác nhận.', 100, false)
            this.clearPolling()
            // Lưu vé và chuyển trang
            const ticket = {
              paymentId: this.mockPaymentId,
              gateway: this.selectedGateway,
              total: this.total,
              tripId: this.tripId,
              seats: this.selectedSeats,
              from: this.fromCity,
              to: this.toCity,
              date: this.travelDate,
              passengers: this.passengers,
              pickupStation: this.pickupStation,
              dropoffStation: this.dropoffStation,
              company: this.company,
              createdAt: Date.now()
            }
            try { localStorage.setItem('dkmn:lastTicket', JSON.stringify(ticket)) } catch (e) {}
            this.$router.push({
              path: '/client-ve-da-dat',
              query: {
                paymentId: ticket.paymentId,
                gateway: ticket.gateway,
                total: ticket.total,
                tripId: ticket.tripId || '',
                seats: (ticket.seats || []).join(','),
                createdAt: ticket.createdAt,
                from: ticket.from || '',
                to: ticket.to || '',
                date: ticket.date || '',
                passengers: ticket.passengers || '',
                pickupStation: ticket.pickupStation || '',
                dropoffStation: ticket.dropoffStation || '',
                company: ticket.company || ''
              }
            })
          } else if (result.status === 'failed' || result.status === 'cancelled') {
            const label = result.status === 'failed' ? 'Thất bại' : 'Đã hủy'
            this.setStatus(result.status, label, result.message || 'Giao dịch không thành công.', 100, false)
            this.clearPolling()
          } else {
            // pending update progress
            const next = Math.min(95, this.status.progress + 8)
            this.setStatus('pending', 'Đang xử lý', 'Đang đợi phản hồi từ cổng thanh toán...', next, true)
          }
        } catch (e) {
          // transient error, keep polling but update detail
          const next = Math.min(95, this.status.progress + 3)
          this.setStatus('pending', 'Đang xử lý', 'Mất kết nối tạm thời, đang thử lại...', next, true)
        }
      }, 1200)
    },
    clearPolling() {
      if (this.pollTimer) {
        clearInterval(this.pollTimer)
        this.pollTimer = null
      }
    },
    // ------- Mock helpers below (replace with real API integration) -------
    async mockCreatePayment() {
      // simulate network
      await new Promise(r => setTimeout(r, 600))
      return { id: 'PM_' + Math.random().toString(36).slice(2), status: 'pending' }
    },
    async mockOpenGateway() {
      // simulate user interaction delay for MoMo
      await new Promise(r => setTimeout(r, 1200))
      return true
    },
    async mockCardAuthorize() {
      // simulate 3DS/checks
      await new Promise(r => setTimeout(r, 1200))
      return { status: 'authorized' }
    },
    async mockCardCapture() {
      // simulate capture/settlement
      await new Promise(r => setTimeout(r, 900))
      return { status: 'captured' }
    },
    async mockQueryStatus() {
      // randomly decide when to finish
      // 65% success, 25% keep pending, 10% fail
      const roll = Math.random()
      if (roll < 0.65 && this.status.progress >= 80) {
        return { status: 'success' }
      }
      if (roll > 0.9 && this.status.progress >= 70) {
        return { status: 'failed', message: 'Cổng thanh toán từ chối giao dịch.' }
      }
      return { status: 'pending' }
    }
  },
  mounted() {
    // Initialize from route query (coming from seat selection)
    const q = this.$route?.query || {}
    if (q.total) {
      const t = parseInt(q.total)
      this.subtotal = Number.isFinite(t) ? t : 0
    }
    if (q.seats) {
      this.selectedSeats = String(q.seats)
        .split(',')
        .map(s => s.trim())
        .filter(Boolean)
    }
    if (q.tripId) {
      this.tripId = String(q.tripId)
    }
    // extra ticket info
    if (q.from) this.fromCity = String(q.from)
    if (q.to) this.toCity = String(q.to)
    if (q.date) this.travelDate = String(q.date)
    if (q.passengers) this.passengers = String(q.passengers)
    if (q.pickupStation) this.pickupStation = String(q.pickupStation)
    if (q.dropoffStation) this.dropoffStation = String(q.dropoffStation)
    if (q.company) this.company = String(q.company)
  },
  beforeUnmount() {
    this.clearPolling()
  }
}
</script>

<style>
/* Scoped page-specific helpers (kept minimal; rely on Bootstrap) */
</style>