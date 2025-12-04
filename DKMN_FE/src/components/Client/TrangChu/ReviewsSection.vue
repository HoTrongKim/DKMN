<template>
  <section class="reviews-section" id="reviews">
    <div class="container-lg">
      <div class="reviews-header">
        <div>
          <span class="reviews-eyebrow">Trải nghiệm thực tế</span>
          <h2>Khách hàng nói gì về DKMN?</h2>
          <p class="reviews-subtitle">
            Những phản hồi đã được duyệt từ khách hàng thực tế giúp bạn an tâm chọn lựa hành trình phù hợp.
          </p>
        </div>

        <div class="reviews-meta">
          <div class="rating-chip">
            <span class="rating-value">{{ averageRating }}</span>
            <div>
              <small>Điểm trung bình</small>
              <div class="rating-stars">
                <i
                  v-for="n in 5"
                  :key="`avg-${n}`"
                  class="bx"
                  :class="n <= Math.round(Number(averageRating)) ? 'bxs-star' : 'bx-star'"
                ></i>
              </div>
            </div>
          </div>
          <div class="reviews-count">
            {{ totalReviews }}+ đánh giá được duyệt
          </div>
        </div>
      </div>

      <div class="reviews-carousel">
        <button class="nav-button" :disabled="!canPrev" @click="changePage(-1)">
          <i class="bx bx-chevron-left"></i>
        </button>

        <div class="reviews-grid" aria-live="polite">
          <div v-if="loading" class="skeleton-grid">
            <div v-for="n in perPage" :key="`skeleton-${n}`" class="review-card skeleton-card"></div>
          </div>

          <template v-else>
            <article
              v-for="review in visibleReviews"
              :key="review.id"
              class="review-card"
            >
              <div class="review-card__header">
                <div class="avatar" :title="review.customer">
                  {{ review.initials }}
                </div>
                <div class="review-card__info">
                  <h4>{{ review.customer }}</h4>
                  <small>{{ review.trip || fallbackTripLabel(review) }}</small>
                </div>
                <span class="review-card__rating">
                  <i class="bx bxs-star"></i>
                  {{ review.rating.toFixed(1) }}
                </span>
              </div>
              <p class="review-card__comment">
                {{ review.comment || "Khách hàng chưa để lại nhận xét chi tiết." }}
              </p>
              <div class="review-card__footer">
                <span>{{ review.operator || "DKMN" }}</span>
                <span>{{ formatDate(review.createdAt) }}</span>
              </div>
            </article>

            <div v-if="!visibleReviews.length && !error" class="empty-state">
              <p>Chưa có đánh giá nào được duyệt. Hãy là người đầu tiên chia sẻ trải nghiệm!</p>
            </div>
          </template>
        </div>

        <button class="nav-button" :disabled="!canNext" @click="changePage(1)">
          <i class="bx bx-chevron-right"></i>
        </button>
      </div>

      <div class="reviews-footer">
        <div class="dots" role="tablist" aria-label="Trang đánh giá">
          <span
            v-for="n in totalPages"
            :key="`dot-${n}`"
            :class="['dot', { active: n - 1 === page }]"
            role="tab"
            :aria-selected="n - 1 === page"
          ></span>
        </div>
        <router-link to="/client-ve-da-dat" class="cta-link">
          Gửi đánh giá của bạn <i class="bx bx-right-arrow-alt"></i>
        </router-link>
      </div>

      <div v-if="error" class="alert alert-danger text-center mt-3">{{ error }}</div>
    </div>
  </section>
</template>

<script>
import api from "../../../services/api";

const REVIEWS_ENDPOINT = "/dkmn/danh-gia/get-data";

export default {
  name: "ReviewsSection",
  data() {
    return {
      reviews: [],
      loading: false,
      error: "",
      page: 0,
      perPage: 3,
    };
  },
  computed: {
    visibleReviews() {
      const start = this.page * this.perPage;
      return this.reviews.slice(start, start + this.perPage);
    },
    totalPages() {
      return Math.max(1, Math.ceil(this.reviews.length / this.perPage));
    },
    canPrev() {
      return this.page > 0;
    },
    canNext() {
      return this.page < this.totalPages - 1;
    },
    totalReviews() {
      return this.reviews.length;
    },
    averageRating() {
      if (!this.reviews.length) {
        return "5.0";
      }
      const total = this.reviews.reduce((sum, item) => sum + item.rating, 0);
      return (total / this.reviews.length).toFixed(1);
    },
  },
  methods: {
    /**
     * Lấy danh sách đánh giá từ khách hàng.
     * 
     * API: `GET /dkmn/danh-gia/get-data`
     * Backend Controller: `DanhGiaController::getData` (dự đoán)
     * 
     * Logic:
     * - Gọi API với tham số limit (mặc định 12).
     * - Map dữ liệu trả về qua `normalizeReview` để chuẩn hóa.
     * - Reset phân trang về trang đầu tiên.
     */
    async fetchReviews() {
      this.loading = true;
      this.error = "";
      try {
        const { data } = await api.get(REVIEWS_ENDPOINT, { params: { limit: 12 } });
        const list = data?.data || data || [];
        this.reviews = list
          .map((item) => this.normalizeReview(item))
          .filter((review) => review && review.rating);
        this.page = 0;
      } catch (error) {
        console.error("fetchReviews error", error);
        this.error =
          error?.response?.data?.message ||
          "Không thể tải đánh giá. Vui lòng thử lại sau.";
      } finally {
        this.loading = false;
      }
    },
    normalizeReview(raw) {
      if (!raw || typeof raw !== "object") {
        return null;
      }
      const ratingValue = Number(raw.rating ?? raw.diem ?? 0);
      const customerName = (raw.customer || raw.customerName || "")
        .toString()
        .trim();
      const fallbackName =
        customerName ||
        `Khách hàng #${raw.nguoi_dung_id || raw.id || Math.floor(Math.random() * 9999)}`;

      return {
        id: raw.id ?? raw.danh_gia_id ?? `${Date.now()}-${Math.random()}`,
        rating: ratingValue > 0 ? ratingValue : 5,
        comment: raw.comment || raw.nhan_xet || "",
        customer: fallbackName,
        trip: raw.trip || raw.route || null,
        tripId: raw.tripId || raw.chuyen_di_id || null,
        operator: raw.operator || raw.operatorName || raw.nha_van_hanh || "",
        createdAt: raw.createdAt || raw.ngay_tao || null,
        initials: this.buildInitials(fallbackName),
      };
    },
    buildInitials(name) {
      if (!name || typeof name !== "string") {
        return "KH";
      }
      const parts = name
        .trim()
        .split(/\s+/)
        .filter(Boolean);
      const first = parts[0]?.charAt(0) || "K";
      const last = parts.length > 1 ? parts[parts.length - 1].charAt(0) : "";
      return (first + last).toUpperCase() || "KH";
    },
    formatDate(value) {
      if (!value) return "Vừa cập nhật";
      const date = new Date(value);
      if (Number.isNaN(date.getTime())) {
        return value;
      }
      return date.toLocaleDateString("vi-VN", {
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
      });
    },
    changePage(delta) {
      const next = this.page + delta;
      if (next >= 0 && next < this.totalPages) {
        this.page = next;
      }
    },
    fallbackTripLabel(review) {
      if (review.tripId) {
        return `Chuyến đi #${review.tripId}`;
      }
      return "Hành trình DKMN";
    },
  },
  mounted() {
    this.fetchReviews();
  },
};
</script>

<style scoped>
.reviews-section {
  background: linear-gradient(180deg, #0b1224, #050c1d);
  color: #e2e8f0;
  padding: clamp(3rem, 6vw, 5rem) 0 clamp(4rem, 7vw, 6rem);
  width: 100vw;
  margin-left: calc(50% - 50vw);
  margin-right: calc(50% - 50vw);
}

.reviews-header {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
  gap: 1.5rem;
  margin-bottom: 2.5rem;
}

.reviews-eyebrow {
  text-transform: uppercase;
  letter-spacing: 0.35em;
  font-size: 0.78rem;
  color: #60a5fa;
  display: inline-block;
  margin-bottom: 0.5rem;
}

.reviews-subtitle {
  color: #cbd5e1;
  max-width: 520px;
  margin-bottom: 0;
}

.reviews-meta {
  display: flex;
  gap: 1.5rem;
  align-items: center;
}

.rating-chip {
  display: flex;
  gap: 0.85rem;
  align-items: center;
  background: rgba(59, 130, 246, 0.15);
  border: 1px solid rgba(59, 130, 246, 0.35);
  border-radius: 16px;
  padding: 0.85rem 1.25rem;
}

.rating-value {
  font-size: 2.25rem;
  font-weight: 700;
  color: #fcd34d;
  line-height: 1;
}

.rating-stars .bx {
  color: #fbbf24;
  font-size: 1rem;
  margin-right: 0.15rem;
}

.reviews-count {
  font-weight: 600;
  color: #cbd5e1;
}

.reviews-carousel {
  display: grid;
  grid-template-columns: auto 1fr auto;
  gap: 1rem;
  align-items: center;
}

.nav-button {
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.15);
  color: #e2e8f0;
  border-radius: 999px;
  width: 48px;
  height: 48px;
  display: grid;
  place-items: center;
  transition: all 0.2s ease;
}

.nav-button:disabled {
  opacity: 0.35;
  cursor: not-allowed;
}

.nav-button:not(:disabled):hover {
  background: rgba(59, 130, 246, 0.25);
  border-color: rgba(59, 130, 246, 0.5);
  transform: translateY(-2px);
}

.reviews-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
  gap: 1.5rem;
}

.review-card {
  background: rgba(15, 23, 42, 0.85);
  border: 1px solid rgba(148, 163, 184, 0.25);
  border-radius: 24px;
  padding: 1.75rem;
  box-shadow: 0 25px 60px rgba(2, 6, 23, 0.45);
  display: flex;
  flex-direction: column;
  gap: 1rem;
  min-height: 230px;
}

.review-card__header {
  display: flex;
  align-items: center;
  gap: 0.9rem;
}

.avatar {
  width: 48px;
  height: 48px;
  border-radius: 14px;
  background: linear-gradient(135deg, #3b82f6, #22d3ee);
  font-weight: 700;
  color: #0b1224;
  display: flex;
  align-items: center;
  justify-content: center;
}

.review-card__info h4 {
  font-size: 1rem;
  margin: 0;
  color: #f8fafc;
}

.review-card__info small {
  color: #94a3b8;
}

.review-card__rating {
  margin-left: auto;
  background: rgba(251, 191, 36, 0.15);
  color: #fbbf24;
  font-weight: 700;
  padding: 0.35rem 0.6rem;
  border-radius: 12px;
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
}

.review-card__comment {
  color: #e2e8f0;
  font-size: 0.98rem;
  line-height: 1.6;
  flex: 1;
}

.review-card__footer {
  display: flex;
  justify-content: space-between;
  font-size: 0.9rem;
  color: #94a3b8;
}

.skeleton-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
  gap: 1.5rem;
}

.skeleton-card {
  position: relative;
  overflow: hidden;
}

.skeleton-card::after {
  content: "";
  position: absolute;
  inset: 0;
  background: linear-gradient(120deg, rgba(255, 255, 255, 0), rgba(255, 255, 255, 0.15), rgba(255, 255, 255, 0));
  animation: shimmer 1.4s infinite;
}

@keyframes shimmer {
  0% {
    transform: translateX(-100%);
  }
  100% {
    transform: translateX(100%);
  }
}

.reviews-footer {
  margin-top: 2.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1rem;
}

.dots {
  display: flex;
  gap: 0.5rem;
}

.dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.25);
  transition: transform 0.2s ease, background 0.2s ease;
}

.dot.active {
  transform: scale(1.2);
  background: #38bdf8;
}

.cta-link {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  color: #60a5fa;
  font-weight: 600;
}

.empty-state {
  grid-column: 1 / -1;
  text-align: center;
  padding: 2rem;
  border-radius: 20px;
  border: 1px dashed rgba(148, 163, 184, 0.35);
  color: #94a3b8;
}

@media (max-width: 768px) {
  .reviews-header {
    flex-direction: column;
  }

  .reviews-meta {
    flex-direction: column;
    align-items: flex-start;
  }

  .reviews-carousel {
    grid-template-columns: 1fr;
  }

  .nav-button {
    order: -1;
    justify-self: flex-end;
  }
}
</style>

