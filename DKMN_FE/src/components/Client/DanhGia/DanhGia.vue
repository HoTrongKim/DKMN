<template>
  <div class="container py-4">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <div class="card shadow-sm">
          <div class="card-header bg-white d-flex align-items-center justify-content-between">
            <h5 class="mb-0">Đánh giá chuyến đi</h5>
            <span class="badge" :class="canRate ? 'bg-primary' : 'bg-secondary'">{{ canRate ? 'Có thể đánh giá' : 'Không thể đánh giá' }}</span>
          </div>
          <div class="card-body">
            <div v-if="!lastTicket" class="text-center py-4">
              <div class="mb-2">Chưa tìm thấy thông tin vé gần đây.</div>
              <router-link class="btn btn-outline-primary" to="/client-ve-da-dat">Xem vé đã đặt</router-link>
            </div>

            <div v-else>
              <ul class="list-group list-group-flush mb-3">
                <li class="list-group-item d-flex justify-content-between"><span>Mã chuyến</span><strong>{{ lastTicket.tripId || '-' }}</strong></li>
                <li class="list-group-item d-flex justify-content-between"><span>Hành trình</span><span class="text-muted">{{ lastTicket.from || '-' }} → {{ lastTicket.to || '-' }}</span></li>
                <li class="list-group-item d-flex justify-content-between"><span>Ngày đi</span><span class="text-muted">{{ lastTicket.date || '-' }}</span></li>
              </ul>

              <div v-if="alreadyRated" class="alert alert-success d-flex align-items-center" role="alert">
                <i class="bx bx-check-circle me-2"></i>
                Bạn đã đánh giá chuyến này trước đó. Cảm ơn bạn!
              </div>

              <div class="mb-3">
                <label class="form-label fw-semibold">Mức độ hài lòng</label>
                <div class="d-flex align-items-center gap-2">
                  <button
                    v-for="n in 5"
                    :key="n"
                    type="button"
                    class="btn"
                    :class="n <= rating ? 'btn-warning' : 'btn-outline-secondary'"
                    @click="setRating(n)"
                    :disabled="!canRate || submitting || alreadyRated"
                  >
                    <i class="bx" :class="n <= rating ? 'bxs-star' : 'bx-star'" style="font-size: 1.25rem"></i>
                  </button>
                  <span class="ms-2 text-muted">{{ rating }} / 5</span>
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label fw-semibold">Nhận xét thêm (tuỳ chọn)</label>
                <textarea class="form-control" rows="4" v-model.trim="comment" :disabled="!canRate || submitting || alreadyRated" placeholder="Ví dụ: Xe sạch sẽ, tài xế thân thiện..."></textarea>
              </div>

              <div class="d-flex gap-2">
                <button class="btn btn-primary" @click="submitRating" :disabled="!canRate || rating === 0 || submitting || alreadyRated">
                  <span v-if="!submitting">Gửi đánh giá</span>
                  <span v-else>Đang gửi...</span>
                </button>
                <router-link to="/client-ve-da-dat" class="btn btn-outline-secondary">Quay lại vé</router-link>
              </div>

              <div v-if="message" class="form-text mt-3" :class="{'text-success': messageType==='success', 'text-danger': messageType==='error'}">{{ message }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'DanhGia',
  data() {
    return {
      lastTicket: null,
      rating: 0,
      comment: '',
      submitting: false,
      message: '',
      messageType: 'success',
      alreadyRated: false
    }
  },
  computed: {
    canRate() {
      if (!this.lastTicket) return false
      // Điều kiện đơn giản: chỉ cho đánh giá nếu có tripId và vé đã tạo cách đây > 1 phút
      const created = Number(this.lastTicket.createdAt || 0)
      const oneMinute = 60 * 1000
      const finished = Date.now() - created > oneMinute
      return !!this.lastTicket.tripId && finished
    }
  },
  methods: {
    setRating(n) {
      this.rating = n
    },
    loadLastTicket() {
      try {
        const raw = localStorage.getItem('dkmn:lastTicket')
        if (raw) this.lastTicket = JSON.parse(raw)
      } catch (e) {
        this.lastTicket = null
      }
    },
    checkDuplicate() {
      if (!this.lastTicket || !this.lastTicket.tripId) return false
      try {
        const raw = localStorage.getItem('dkmn:ratings')
        const list = raw ? JSON.parse(raw) : []
        return list.some(r => r.tripId === this.lastTicket.tripId)
      } catch (e) {
        return false
      }
    },
    saveRating(r) {
      try {
        const raw = localStorage.getItem('dkmn:ratings')
        const list = raw ? JSON.parse(raw) : []
        list.push(r)
        localStorage.setItem('dkmn:ratings', JSON.stringify(list))
      } catch (e) {
        // ignore
      }
    },
    async submitRating() {
      if (!this.canRate || this.rating === 0 || this.submitting) return
      if (this.checkDuplicate()) {
        this.alreadyRated = true
        this.message = 'Bạn đã đánh giá chuyến này.'
        this.messageType = 'error'
        return
      }
      this.submitting = true
      this.message = ''
      try {
        // TODO: Gọi API backend khi có endpoint. Tạm thời lưu localStorage.
        const payload = {
          tripId: this.lastTicket.tripId,
          paymentId: this.lastTicket.paymentId,
          rating: this.rating,
          comment: this.comment,
          createdAt: Date.now()
        }
        // Ví dụ: await axios.post('/api/ratings', payload)
        this.saveRating(payload)
        this.alreadyRated = true
        this.message = 'Cảm ơn bạn đã gửi đánh giá.'
        this.messageType = 'success'
      } catch (e) {
        this.message = 'Không thể gửi đánh giá. Vui lòng thử lại.'
        this.messageType = 'error'
      } finally {
        this.submitting = false
      }
    }
  },
  mounted() {
    this.loadLastTicket()
    this.alreadyRated = this.checkDuplicate()
  }
}
</script>

<style>
</style>


