<template>
  <div class="reviews-section-premium">
    <div class="container reviews-container-wrapper">
      <div class="section-header-fancy" data-aos="fade-up">
        <span class="section-eyebrow">Đánh giá từ khách hàng</span>
        <h2 class="section-title-gradient">Khách hàng nói gì về chúng tôi?</h2>
        <p class="text-muted mt-2">Hàng ngàn hành khách đã tin tưởng lựa chọn DKMN</p>
      </div>

      <div v-if="isLoading" class="text-center py-5">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
      </div>

      <div v-else class="reviews-wrapper position-relative">
        <div class="reviews-carousel-container" ref="carousel">
          <div v-if="reviews.length === 0" class="text-center w-100 py-4 text-muted">
            Chưa có đánh giá nào.
          </div>
          
          <div 
            v-for="review in reviews" 
            :key="review.id" 
            class="review-card-modern"
          >
            <div class="review-header">
              <div class="reviewer-avatar">
                {{ getInitials(review.customer) }}
              </div>
              <div class="reviewer-info">
                <h4>{{ review.customer }}</h4>
                <div class="review-stars">
                  <i 
                    v-for="n in 5" 
                    :key="n" 
                    class="bx" 
                    :class="n <= Math.round(review.rating) ? 'bxs-star' : 'bx-star'"
                  ></i>
                </div>
              </div>
            </div>
            
            <div class="review-content">
              {{ review.comment }}
            </div>

            <div class="review-trip-badge" v-if="review.trip">
              <i class="bx bx-map"></i>
              <span class="text-truncate" style="max-width: 200px;">{{ review.trip }}</span>
            </div>
             <small class="text-muted mt-2 d-block ms-1" style="font-size: 0.75rem">
               {{ formatDate(review.createdAt) }}
             </small>
          </div>
        </div>

        <div class="review-nav-actions" v-if="reviews.length > 0">
          <button @click="scrollCarousel(-1)" class="carousel-nav-btn" aria-label="Previous">
            <i class="bx bx-chevron-left"></i>
          </button>
          <button @click="scrollCarousel(1)" class="carousel-nav-btn" aria-label="Next">
            <i class="bx bx-chevron-right"></i>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import api from "../../../services/api";

export default {
  name: "ReviewsSection",
  data() {
    return {
      reviews: [],
      isLoading: false,
    };
  },
  mounted() {
    this.fetchReviews();
  },
  methods: {
    async fetchReviews() {
      this.isLoading = true;
      try {
        const { data } = await api.get('/dkmn/danh-gia/public', {
            params: { limit: 10 }
        });

        // Debug log
        console.log("API Response:", data);

        const reviewsList = data?.data || [];
        
        this.reviews = reviewsList.map(review => ({
          id: review.id,
          rating: Number(review.rating) || 5, // Đảm bảo là số
          comment: review.comment,
          customer: review.customer || "Khách hàng",
          trip: review.trip,
          tripId: review.tripId,
          operator: review.operator, 
          createdAt: review.createdAt
        }));

      } catch (error) {
        console.error("Lỗi khi tải đánh giá:", error);
      } finally {
        this.isLoading = false;
      }
    },
    getInitials(name) {
      if (!name) return 'K';
      const parts = name.trim().split(' ');
      if (parts.length > 0) {
        const last = parts[parts.length - 1];
        return last.charAt(0).toUpperCase();
      }
      return name.charAt(0).toUpperCase();
    },
    formatDate(dateString) {
      if (!dateString) return '';
      try {
        return new Date(dateString).toLocaleDateString('vi-VN');
      } catch (e) {
        return '';
      }
    },
    scrollCarousel(direction) {
      const container = this.$refs.carousel;
      if (container) {
        const scrollAmount = 350 + 32; // card width (350) + gap (32/2rem)
        container.scrollBy({
          left: direction * scrollAmount,
          behavior: 'smooth'
        });
      }
    }
  }
};
</script>

<style scoped>
/* Scoped styles overrides if needed, but keeping main styles in global CSS file */
</style>
