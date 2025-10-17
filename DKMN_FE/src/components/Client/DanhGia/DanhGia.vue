<template>
  <div class="container py-4">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <div class="card shadow-sm">
          <div class="card-header bg-white d-flex align-items-center justify-content-between">
            <h5 class="mb-0">Đánh giá chuyến đi</h5>
            <router-link to="/client-ve-da-dat" class="btn btn-sm btn-outline-secondary">Vé đã đặt</router-link>
          </div>
          <div class="card-body">
            <div v-if="!tripId" class="text-center py-4 text-muted">
              Không tìm thấy mã chuyến. Vui lòng quay lại.
            </div>

            <div v-else>
              <div class="mb-3">
                <div class="small text-muted">Mã chuyến</div>
                <div class="fw-semibold">{{ tripId }}</div>
              </div>

              <div class="mb-3">
                <div class="small text-muted">Hành trình</div>
                <div class="fw-semibold">{{ from }} → {{ to }}</div>
              </div>

              <div class="mb-3">
                <div class="small text-muted">Ngày đi</div>
                <div class="fw-semibold">{{ date }}</div>
              </div>

              <div class="mb-3">
                <div class="small text-muted mb-1">Đánh giá của bạn</div>
                <div class="rating-stars">
                  <i
                    v-for="n in 5"
                    :key="n"
                    class="bx"
                    :class="n <= rating ? 'bxs-star text-warning' : 'bx-star'"
                    @click="setRating(n)"
                  ></i>
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label">Nhận xét</label>
                <textarea v-model="comment" rows="4" class="form-control" placeholder="Chia sẻ trải nghiệm của bạn..."></textarea>
              </div>

              <div class="d-flex gap-2">
                <button class="btn btn-primary" :disabled="submitting || rating === 0" @click="submitRating">Gửi đánh giá</button>
                <router-link class="btn btn-outline-secondary" to="/client-ve-da-dat">Hủy</router-link>
              </div>

              <div v-if="error" class="alert alert-danger mt-3 py-2">{{ error }}</div>
              <div v-if="success" class="alert alert-success mt-3 py-2">{{ success }}</div>
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
      tripId: null,
      from: '',
      to: '',
      date: '',
      rating: 0,
      comment: '',
      submitting: false,
      error: '',
      success: ''
    }
  },
  methods: {
    setRating(n) {
      this.rating = n
    },
    loadFromQuery() {
      const q = this.$route?.query || {}
      this.tripId = q.tripId ? String(q.tripId) : null
      this.from = q.from ? String(q.from) : ''
      this.to = q.to ? String(q.to) : ''
      this.date = q.date ? String(q.date) : ''
    },
    getStoredRatings() {
      try {
        const raw = localStorage.getItem('dkmn:ratings')
        return raw ? JSON.parse(raw) : []
      } catch (e) {
        return []
      }
    },
    setStoredRatings(items) {
      try {
        localStorage.setItem('dkmn:ratings', JSON.stringify(items))
      } catch (e) {}
    },
    submitRating() {
      if (!this.tripId || this.rating === 0) return
      this.submitting = true
      this.error = ''
      this.success = ''

      const items = this.getStoredRatings()
      const exists = items.find(r => String(r.tripId) === String(this.tripId))
      if (exists) {
        this.submitting = false
        this.error = 'Bạn đã đánh giá chuyến này rồi.'
        return
      }

      const userInfo = (() => {
        try { return JSON.parse(localStorage.getItem('userInfo') || '{}') } catch (e) { return {} }
      })()

      const payload = {
        id: Date.now(),
        tripId: this.tripId,
        rating: this.rating,
        comment: this.comment,
        from: this.from,
        to: this.to,
        date: this.date,
        userId: userInfo?.id || null,
        createdAt: Date.now()
      }
      items.push(payload)
      this.setStoredRatings(items)

      this.submitting = false
      this.success = 'Cảm ơn bạn đã đánh giá!'

      // Optionally navigate back after a delay
      setTimeout(() => {
        this.$router.push({ path: '/client-ve-da-dat', query: { tripId: this.tripId } })
      }, 800)
    }
  },
  mounted() {
    this.loadFromQuery()
  }
}
</script>

<style>
.rating-stars .bx {
  font-size: 28px;
  cursor: pointer;
  margin-right: 4px;
}
</style>


