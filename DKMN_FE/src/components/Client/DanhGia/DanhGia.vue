<template>
  <div class="container py-4">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <div class="card shadow-sm">
          <div class="card-header bg-white d-flex align-items-center justify-content-between">
            <h5 class="mb-0">Đánh giá chuyến đi</h5>
            <router-link to="/client-ve-da-dat" class="btn btn-sm btn-outline-secondary">
              Vé đã đặt
            </router-link>
          </div>

          <div class="card-body">
            <div v-if="!tripId" class="text-center py-4 text-muted">
              Không tìm thấy thông tin chuyến đi. Vui lòng quay lại.
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

              <div class="alert alert-info py-2" v-if="alreadyRated">
                Bạn đã gửi đánh giá này (trạng thái: {{ statusLabel(existingStatus) }}).
              </div>

              <div class="mb-3">
                <div class="small text-muted mb-1">Đánh giá của bạn</div>
                <div class="rating-stars">
                  <i
                    v-for="n in 5"
                    :key="n"
                    class="bx"
                    :class="n <= rating ? 'bxs-star text-warning' : 'bx-star'"
                    @click="!alreadyRated && setRating(n)"
                  ></i>
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label">Nhận xét</label>
                <textarea
                  v-model="comment"
                  rows="4"
                  class="form-control"
                  placeholder="Chia sẻ trải nghiệm của bạn..."
                  :disabled="alreadyRated"
                ></textarea>
              </div>

              <div class="d-flex gap-2">
                <button class="btn btn-primary" :disabled="!canSubmit" @click="submitRating">
                  <span v-if="submitting" class="spinner-border spinner-border-sm me-2"></span>
                  Gửi đánh giá
                </button>
                <router-link class="btn btn-outline-secondary" to="/client-ve-da-dat">Hủy</router-link>
              </div>

              <div v-if="error" class="alert alert-danger mt-3 py-2">{{ error }}</div>
              <div v-if="success" class="alert alert-success mt-3 py-2">{{ success }}</div>
              <div v-if="loading" class="text-muted small mt-3">
                <i class="bx bx-loader bx-spin me-1"></i> Đang tải dữ liệu đánh giá...
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import api from '../../../services/api'

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
      loading: false,
      error: '',
      success: '',
      alreadyRated: false,
      existingStatus: '',
    }
  },
  computed: {
    canSubmit() {
      return !this.alreadyRated && this.rating > 0 && !this.submitting
    },
  },
  methods: {
    setRating(n) {
      this.rating = n
    },
    statusLabel(status) {
      return (
        {
          chap_nhan: 'Đã duyệt',
          tu_choi: 'Đã ẩn',
          cho_duyet: 'Chờ duyệt',
        }[status] || 'Chờ duyệt'
      )
    },
    loadFromQuery() {
      const q = this.$route?.query || {}
      this.tripId = q.tripId ? String(q.tripId) : null
      this.from = q.from ? String(q.from) : ''
      this.to = q.to ? String(q.to) : ''
      this.date = q.date ? String(q.date) : ''
    },
    async checkExistingRating() {
      if (!this.tripId) return
      this.loading = true
      try {
        const { data } = await api.get('/ratings/me', { params: { tripId: this.tripId } })
        const record = data?.data?.[0]
        if (record) {
          this.alreadyRated = true
          this.existingStatus = record.status
          this.rating = record.rating
          this.comment = record.comment || ''
        }
      } catch (error) {
        console.warn('Cannot fetch rating info', error)
      } finally {
        this.loading = false
      }
    },
    persistLocalRating() {
      try {
        const raw = localStorage.getItem('dkmn:ratings')
        const arr = raw ? JSON.parse(raw) : []
        const idx = arr.findIndex((item) => String(item.tripId) === String(this.tripId))
        const payload = { tripId: this.tripId, rating: this.rating, comment: this.comment }
        if (idx >= 0) {
          arr[idx] = payload
        } else {
          arr.push(payload)
        }
        localStorage.setItem('dkmn:ratings', JSON.stringify(arr))
      } catch (error) {
        console.warn('Cannot persist rating cache', error)
      }
    },
    async submitRating() {
      if (!this.tripId || this.rating === 0 || this.submitting) return
      this.submitting = true
      this.error = ''
      this.success = ''

      try {
        await api.post('/ratings', {
          tripId: Number(this.tripId),
          rating: this.rating,
          comment: this.comment,
        })

        this.success =
          'Cảm ơn bạn đã đánh giá! Đánh giá sẽ được duyệt và hiển thị sau khi được xét duyệt.'
        this.alreadyRated = true
        this.existingStatus = 'cho_duyet'
        this.persistLocalRating()

        setTimeout(() => {
          this.$router.push({ path: '/client-ve-da-dat' })
        }, 1200)
      } catch (error) {
        this.error =
          error.response?.data?.message || 'Không thể gửi đánh giá. Vui lòng thử lại.'
      } finally {
        this.submitting = false
      }
    },
  },
  mounted() {
    this.loadFromQuery()
    this.checkExistingRating()
  },
}
</script>

<style scoped>
.rating-stars .bx {
  font-size: 30px;
  cursor: pointer;
  margin-right: 4px;
  transition: transform 0.1s ease;
}
.rating-stars .bx:hover {
  transform: scale(1.1);
}
</style>
