<template>
  <div class="rating-page">
    <div class="rating-card shadow-lg">
      <div class="rating-card__header">
        <div>
          <p class="eyebrow">Đánh giá</p>
          <h3>Chuyến đi của bạn</h3>
        </div>
        <router-link to="/client-ve-da-dat" class="btn btn-sm btn-outline-primary rounded-pill">
          Vé đã đặt
        </router-link>
      </div>

      <div v-if="!tripId" class="text-center py-5 text-muted">
        Không tìm thấy thông tin chuyến đi. Vui lòng quay lại.
      </div>

      <div v-else class="rating-content">
        <div class="trip-grid">
          <div>
            <div class="label-muted">Mã chuyến</div>
            <div class="fw-semibold">{{ tripId }}</div>
          </div>
          <div>
            <div class="label-muted">Hành trình</div>
            <div class="fw-semibold">{{ from }} → {{ to }}</div>
          </div>
          <div>
            <div class="label-muted">Ngày đi</div>
            <div class="fw-semibold">{{ date }}</div>
          </div>
        </div>

        <div class="alert alert-info mb-3" v-if="alreadyRated">
          Bạn đã gửi đánh giá này (trạng thái: {{ statusLabel(existingStatus) }}).
        </div>

        <div class="mb-4">
          <div class="label-muted mb-2">Đánh giá của bạn</div>
          <div class="rating-stars">
            <i
              v-for="n in 5"
              :key="n"
              class="bx"
              :class="n <= rating ? 'bxs-star active-star' : 'bx-star'"
              @click="!alreadyRated && setRating(n)"
            ></i>
          </div>
        </div>

        <div class="mb-4">
          <label class="form-label fw-semibold">Nhận xét</label>
          <textarea
            v-model="comment"
            rows="4"
            class="form-control fancy-textarea"
            placeholder="Chia sẻ trải nghiệm của bạn..."
            :disabled="alreadyRated"
          ></textarea>
        </div>

        <div class="d-flex gap-2">
          <button class="btn btn-primary px-4" :disabled="!canSubmit" @click="submitRating">
            <span v-if="submitting" class="spinner-border spinner-border-sm me-2"></span>
            Gửi đánh giá
          </button>
          <router-link class="btn btn-light border" to="/client-ve-da-dat">Huỷ</router-link>
        </div>

        <div v-if="error" class="alert alert-danger mt-3 py-2">{{ error }}</div>
        <div v-if="success" class="alert alert-success mt-3 py-2">{{ success }}</div>
        <div v-if="loading" class="text-muted small mt-3">
          <i class="bx bx-loader bx-spin me-1"></i> Đang tải dữ liệu đánh giá...
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
          tu_choi: 'Đã từ chối',
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
          'Cảm ơn bạn đã đánh giá! Đánh giá sẽ được duyệt và hiển thị sau khi xét duyệt.'
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
/* ------ THEME ------ */
.rating-page {
  --primary: #2f9bff;
  --primary-strong: #1f7bff;
  --primary-soft: #e3f2ff;
  --primary-soft-strong: #c7e0ff;
  --border-soft: #dbeafe;
  --text-main: #0f172a;
  --text-muted: #64748b;
}

/* ------ LAYOUT ------ */
.rating-page {
  background: linear-gradient(180deg, var(--primary-soft) 0%, #f5f9ff 100%);
  padding: 28px 12px 32px;
}

/* canh giữa theo chiều ngang, không kéo full chiều cao */
.rating-card {
  width: 100%;
  max-width: 840px;
  margin: 0 auto;
  background: #ffffff;
  border-radius: 18px;
  padding: 24px 22px 26px;
  border: 1px solid var(--border-soft);
  box-shadow: 0 14px 32px rgba(15, 23, 42, 0.06);
}


.rating-card {
  width: 100%;
  max-width: 840px;
  background: #ffffff;
  border-radius: 18px;
  padding: 24px 22px 26px;
  border: 1px solid var(--border-soft);
  box-shadow: 0 14px 32px rgba(15, 23, 42, 0.06);
}

/* ------ HEADER ------ */
.rating-card__header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 18px;
}

.rating-card__header h3 {
  margin: 0;
  font-weight: 700;
  color: var(--text-main);
}

.eyebrow {
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: var(--text-muted);
  font-size: 0.75rem;
  margin: 0 0 4px;
}

/* ------ CONTENT BOX ------ */
.rating-content {
  border-radius: 14px;
  padding: 14px 16px 16px;
  background: linear-gradient(
    130deg,
    rgba(47, 155, 255, 0.06),
    rgba(219, 234, 254, 0.8)
  );
  border: 1px solid var(--border-soft);
  margin-bottom: 12px;
}

.trip-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 10px 16px;
  margin-bottom: 12px;
}

.label-muted {
  font-size: 0.85rem;
  color: var(--text-muted);
}

/* ------ STARS ------ */
.rating-stars {
  display: flex;
  align-items: center;
}

.rating-stars .bx {
  font-size: 30px;
  cursor: pointer;
  margin-right: 6px;
  transition: transform 0.15s ease, color 0.15s ease;
  color: #cbd5f5;
}

.rating-stars .bx:hover {
  transform: translateY(-2px) scale(1.05);
  color: #fbbf24;
}

.active-star {
  color: #f59e0b !important;
}

/* ------ TEXTAREA ------ */
.fancy-textarea {
  border-radius: 12px;
  border: 1.5px solid #e2e8f0;
  box-shadow: inset 0 1px 1px rgba(15, 23, 42, 0.02);
  transition: border 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
  background-color: #ffffff;
}

.fancy-textarea:focus {
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(47, 155, 255, 0.22);
  background-color: #f9fcff;
}

/* ------ BUTTONS ------ */
.btn-outline-primary {
  border-color: var(--primary);
  color: var(--primary);
}

.btn-outline-primary:hover {
  background: var(--primary-soft);
  color: var(--primary-strong);
}

.btn-primary {
  background: linear-gradient(120deg, var(--primary), var(--primary-strong));
  border: none;
}

.btn-primary:hover {
  background: linear-gradient(120deg, var(--primary-strong), var(--primary));
}

/* ------ ALERTS ------ */
.alert-info {
  background: var(--primary-soft);
  border: 1px solid var(--primary-soft-strong);
  color: #1d4ed8;
}

/* ------ RESPONSIVE ------ */
@media (max-width: 576px) {
  .rating-card {
    padding: 20px 16px 22px;
  }

  .rating-card__header {
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
  }

  .rating-page {
    padding: 22px 12px;
  }
}
</style>
