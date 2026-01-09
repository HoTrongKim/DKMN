<template>
  <div class="popular-routes-section">
    <div class="container">
      <div class="section-header-fancy mb-4 " data-aos="fade-up">
        <span class="section-eyebrow">Điểm đến hấp dẫn</span>
        <h2 class="section-title-gradient">Tuyến đường phổ biến</h2>
      </div>

      <div class="routes-grid">
        <div 
            v-for="(route, index) in routes" 
            :key="index" 
            class="route-card"
            @click="selectRoute(route)"
        >
            <img :src="route.image" :alt="route.name" loading="lazy">
            <div class="route-badge">{{ route.type }}</div>
            <div class="route-overlay">
                <h3 class="route-name">{{ route.name }}</h3>
                <div class="route-meta">
                    <span class="route-price">Từ {{ formatPrice(route.price) }}</span>
                    <span class="route-rating"><i class='bx bxs-star'></i> {{ route.rating }}</span>
                </div>
            </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
// Reuse existing assets
import imgBus1 from '../../../assets/images/hero/bus-travel-1.png';
import imgBus2 from '../../../assets/images/hero/bus-travel-2.png';
import imgTrain from '../../../assets/images/hero/train-travel.png';
import imgPlane from '../../../assets/images/hero/plane-travel.png';

export default {
  name: "PopularRoutes",
  data() {
    return {
      routes: [
        {
          name: "Sài Gòn - Đà Lạt",
          image: imgBus1,
          price: 250000,
          rating: 4.8,
          type: "Xe Khách",
          fromId: 32, // HCM
          toId: 35 // Lam Dong (Example) - logic to handled by parent if needed
        },
        {
          name: "Hà Nội - Sapa",
          image: imgBus2,
          price: 350000,
          rating: 4.9,
          type: "Xe Giường Nằm",
          fromId: 1, // HN
          toId: 10 // Lao Cai
        },
        {
          name: "Đà Nẵng - Huế",
          image: imgTrain,
          price: 120000,
          rating: 4.7,
          type: "Tàu Hỏa",
          fromId: 15, // DN
          toId: 16 // Hue
        },
        {
          name: "Sài Gòn - Nha Trang",
          image: imgPlane,
          price: 850000,
          rating: 4.9,
          type: "Máy Bay",
          fromId: 32, // HCM
          toId: 28 // Khanh Hoa
        }
      ]
    };
  },
  methods: {
    formatPrice(value) {
      return new Intl.NumberFormat("vi-VN", {
        style: "currency",
        currency: "VND",
      }).format(value);
    },
    selectRoute(route) {
        // Just emit for now, simple implementation
        this.$emit('select-route', route);
    }
  }
};
</script>
