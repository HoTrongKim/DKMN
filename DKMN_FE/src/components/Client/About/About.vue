<template>
  <div class="about-page">
    <!-- Hero Section -->
    <section class="hero-section">
      <div class="hero-background"></div>
      <div class="container">
        <div class="hero-content">
          <h1 class="hero-title">
            <span class="gradient-text">Về DKMN</span>
          </h1>
          <p class="hero-subtitle">
            Nền tảng đặt vé du lịch thông minh, hiện đại và tiện lợi
          </p>
        </div>
      </div>
    </section>

    <!-- Mission Section -->
    <section class="mission-section">
      <div class="container">
        <div class="section-header">
          <h2 class="section-title">Sứ mệnh của chúng tôi</h2>
          <p class="section-description">
            DKMN được xây dựng với mục tiêu mang đến trải nghiệm đặt vé tốt nhất cho mọi hành khách
          </p>
        </div>

        <div class="mission-grid">
          <div class="mission-card" v-for="(mission, index) in missions" :key="index">
            <div class="mission-icon">
              <i :class="mission.icon"></i>
            </div>
            <h3 class="mission-title">{{ mission.title }}</h3>
            <p class="mission-description">{{ mission.description }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Features Section -->
    <section class="features-section">
      <div class="container">
        <div class="section-header">
          <h2 class="section-title">Tại sao chọn DKMN?</h2>
        </div>

        <div class="features-grid">
          <div class="feature-card" v-for="(feature, index) in features" :key="index">
            <div class="feature-number">{{ String(index + 1).padStart(2, '0') }}</div>
            <div class="feature-content">
              <h3 class="feature-title">{{ feature.title }}</h3>
              <p class="feature-description">{{ feature.description }}</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
      <div class="container">
        <div class="stats-grid">
          <div class="stat-item" v-for="(stat, index) in stats" :key="index">
            <div class="stat-number">{{ stat.number }}</div>
            <div class="stat-label">{{ stat.label }}</div>
          </div>
        </div>
      </div>
    </section>

    <!-- Values Section -->
    <section class="values-section">
      <div class="container">
        <div class="section-header">
          <h2 class="section-title">Giá trị cốt lõi</h2>
        </div>

        <div class="values-grid">
          <div class="value-card" v-for="(value, index) in values" :key="index">
            <div class="value-icon">
              <i :class="value.icon"></i>
            </div>
            <h3 class="value-title">{{ value.title }}</h3>
            <p class="value-description">{{ value.description }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
      <div class="container">
        <div class="cta-content">
          <h2 class="cta-title">Sẵn sàng bắt đầu hành trình của bạn?</h2>
          <p class="cta-description">
            Đặt vé ngay hôm nay và trải nghiệm dịch vụ đặt vé hiện đại nhất
          </p>
          <router-link to="/" class="cta-button">
            <i class="bx bx-search-alt-2 me-2"></i>
            Tìm vé ngay
          </router-link>
        </div>
      </div>
    </section>
  </div>
</template>

<script>
export default {
  /**
   * Component: About
   * 
   * Mô tả: Trang giới thiệu về DKMN.
   * Loại: Static Component (Không có logic nghiệp vụ phức tạp hay gọi API).
   * Dữ liệu: Các thông tin được hardcode trong `data` (missions, features, stats, values).
   */
  name: 'About',
  data() {
    return {
      missions: [
        {
          icon: 'bx bx-rocket',
          title: 'Đổi mới không ngừng',
          description: 'Luôn cập nhật công nghệ mới nhất để mang đến trải nghiệm tốt nhất cho khách hàng'
        },
        {
          icon: 'bx bx-shield-quarter',
          title: 'An toàn & Bảo mật',
          description: 'Bảo vệ thông tin và giao dịch của khách hàng với hệ thống bảo mật hàng đầu'
        },
        {
          icon: 'bx bx-heart',
          title: 'Dịch vụ tận tâm',
          description: 'Đội ngũ hỗ trợ 24/7 luôn sẵn sàng giải đáp mọi thắc mắc của khách hàng'
        },
        {
          icon: 'bx bx-trending-up',
          title: 'Giá cả hợp lý',
          description: 'So sánh giá từ nhiều nhà vận hành để tìm được vé phù hợp nhất với ngân sách'
        }
      ],
      features: [
        {
          title: 'Đặt vé nhanh chóng',
          description: 'Chỉ với vài cú click, bạn có thể tìm và đặt vé cho hành trình của mình'
        },
        {
          title: 'Thanh toán đa dạng',
          description: 'Hỗ trợ nhiều phương thức thanh toán: MoMo, Ngân hàng, hoặc thanh toán khi lên xe'
        },
        {
          title: 'Xác nhận tức thì',
          description: 'Nhận vé điện tử qua email ngay sau khi đặt vé thành công'
        },
        {
          title: 'Quản lý đơn hàng dễ dàng',
          description: 'Theo dõi và quản lý tất cả các đơn hàng của bạn tại một nơi'
        },
        {
          title: 'Thông báo real-time',
          description: 'Cập nhật thông tin chuyến đi và thông báo quan trọng ngay lập tức'
        },
        {
          title: 'Hỗ trợ 24/7',
          description: 'Đội ngũ chăm sóc khách hàng luôn sẵn sàng hỗ trợ bạn mọi lúc, mọi nơi'
        }
      ],
      stats: [
        { number: '100K+', label: 'Khách hàng tin dùng' },
        { number: '50K+', label: 'Vé đã bán' },
        { number: '100+', label: 'Tuyến đường' },
        { number: '24/7', label: 'Hỗ trợ khách hàng' }
      ],
      values: [
        {
          icon: 'bx bx-check-circle',
          title: 'Minh bạch',
          description: 'Thông tin rõ ràng, giá cả công khai, không phí ẩn'
        },
        {
          icon: 'bx bx-time-five',
          title: 'Nhanh chóng',
          description: 'Quy trình đặt vé được tối ưu để tiết kiệm thời gian của bạn'
        },
        {
          icon: 'bx bx-star',
          title: 'Chất lượng',
          description: 'Chỉ hợp tác với các nhà vận hành uy tín, đảm bảo chất lượng dịch vụ'
        },
        {
          icon: 'bx bx-group',
          title: 'Đồng hành',
          description: 'Luôn lắng nghe và cải thiện dựa trên phản hồi của khách hàng'
        }
      ]
    }
  }
}
</script>

<style scoped>
.about-page {
  min-height: 100vh;
  background: radial-gradient(circle at 18% 18%, rgba(37, 99, 235, 0.2), transparent 40%),
    radial-gradient(circle at 82% 14%, rgba(14, 165, 233, 0.16), transparent 36%),
    linear-gradient(135deg, #0b1224, #0f172a 48%, #0b1224);
}

/* Hero Section */
.hero-section {
  position: relative;
  padding: 120px 0 80px;
  background: radial-gradient(circle at 12% 14%, rgba(56, 189, 248, 0.18), transparent 30%),
    radial-gradient(circle at 82% 18%, rgba(45, 212, 191, 0.16), transparent 32%),
    linear-gradient(180deg, #040d1f 0%, #051a31 42%, #082744 72%, #0b2f57 100%);
  overflow: hidden;
}

.hero-background {
  position: absolute;
  inset: 0;
  background: 
    radial-gradient(circle at 20% 50%, rgba(37, 99, 235, 0.3), transparent 50%),
    radial-gradient(circle at 80% 50%, rgba(139, 92, 246, 0.3), transparent 50%);
  animation: pulse 8s ease-in-out infinite;
}

@keyframes pulse {
  0%, 100% { opacity: 0.5; }
  50% { opacity: 1; }
}

.hero-content {
  text-align: center;
  position: relative;
  z-index: 1;
}

.hero-title {
  font-size: 4rem;
  font-weight: 800;
  margin-bottom: 1.5rem;
  color: #e2e8f0;
}

.gradient-text {
  background: linear-gradient(120deg, #60a5fa, #34d399, #a78bfa);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  background-size: 200% auto;
  animation: gradient 3s linear infinite;
}

@keyframes gradient {
  to { background-position: 200% center; }
}

.hero-subtitle {
  font-size: 1.5rem;
  color: #cbd5e1;
  max-width: 600px;
  margin: 0 auto;
}

/* Container */
.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 2rem;
}

/* Section Header */
.section-header {
  text-align: center;
  margin-bottom: 4rem;
}

.section-title {
  font-size: 2.5rem;
  font-weight: 700;
  color: #e2e8f0;
  margin-bottom: 1rem;
}

.section-description {
  font-size: 1.125rem;
  color: #cbd5e1;
  max-width: 700px;
  margin: 0 auto;
}

/* Mission Section */
.mission-section {
  padding: 80px 0;
  background: transparent;
  color: #e2e8f0;
}

.mission-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 2rem;
}

.mission-card {
  padding: 2rem;
  background: linear-gradient(145deg, #0f172a, #111a2e);
  border-radius: 20px;
  border: 1px solid rgba(148, 163, 184, 0.28);
  text-align: center;
  transition: all 0.3s ease;
  box-shadow: 0 30px 70px rgba(5, 9, 20, 0.35);
}

.mission-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 55px 110px rgba(37, 99, 235, 0.3);
  border-color: rgba(59, 130, 246, 0.35);
}

.mission-icon {
  width: 80px;
  height: 80px;
  margin: 0 auto 1.5rem;
  background: linear-gradient(135deg, #3b82f6, #8b5cf6);
  border-radius: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2.5rem;
  color: #ffffff;
}

.mission-title {
  font-size: 1.25rem;
  font-weight: 600;
  color: #e2e8f0;
  margin-bottom: 0.75rem;
}

.mission-description {
  color: #cbd5e1;
  line-height: 1.6;
}

/* Features Section */
.features-section {
  padding: 80px 0;
  background: transparent;
}

.features-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 2rem;
}

.feature-card {
  display: flex;
  gap: 1.5rem;
  padding: 2rem;
  background: linear-gradient(145deg, #0f172a, #111a2e);
  border-radius: 16px;
  border: 1px solid rgba(148, 163, 184, 0.28);
  transition: all 0.3s ease;
  box-shadow: 0 30px 70px rgba(5, 9, 20, 0.35);
}

.feature-card:hover {
  transform: translateX(8px);
  box-shadow: 0 55px 110px rgba(37, 99, 235, 0.3);
  border-color: rgba(59, 130, 246, 0.35);
}

.feature-number {
  font-size: 3rem;
  font-weight: 800;
  color: rgba(226, 232, 240, 0.3);
  line-height: 1;
  min-width: 60px;
}

.feature-content {
  flex: 1;
}

.feature-title {
  font-size: 1.25rem;
  font-weight: 600;
  color: #e2e8f0;
  margin-bottom: 0.5rem;
}

.feature-description {
  color: #cbd5e1;
  line-height: 1.6;
}

/* Stats Section */
.stats-section {
  padding: 80px 0;
  background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 3rem;
  text-align: center;
}

.stat-item {
  padding: 2rem;
}

.stat-number {
  font-size: 4rem;
  font-weight: 800;
  background: linear-gradient(120deg, #60a5fa, #34d399);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  margin-bottom: 0.5rem;
}

.stat-label {
  font-size: 1.125rem;
  color: #cbd5e1;
  font-weight: 500;
}

/* Values Section */
.values-section {
  padding: 80px 0;
  background: transparent;
}

.values-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 2rem;
}

.value-card {
  padding: 2rem;
  background: linear-gradient(145deg, #0f172a, #111a2e);
  border-radius: 16px;
  border: 1px solid rgba(148, 163, 184, 0.28);
  text-align: center;
  transition: all 0.3s ease;
  box-shadow: 0 30px 70px rgba(5, 9, 20, 0.35);
}

.value-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 55px 110px rgba(37, 99, 235, 0.3);
  border-color: rgba(59, 130, 246, 0.35);
}

.value-icon {
  width: 70px;
  height: 70px;
  margin: 0 auto 1.5rem;
  background: linear-gradient(135deg, #3b82f6, #8b5cf6);
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2rem;
  color: #ffffff;
}

.value-title {
  font-size: 1.25rem;
  font-weight: 600;
  color: #e2e8f0;
  margin-bottom: 0.75rem;
}

.value-description {
  color: #cbd5e1;
  line-height: 1.6;
}

/* CTA Section */
.cta-section {
  padding: 100px 0;
  background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
}

.cta-content {
  text-align: center;
  color: #ffffff;
}

.cta-title {
  font-size: 2.5rem;
  font-weight: 700;
  margin-bottom: 1rem;
}

.cta-description {
  font-size: 1.25rem;
  margin-bottom: 2rem;
  opacity: 0.95;
}

.cta-button {
  display: inline-flex;
  align-items: center;
  padding: 1rem 2.5rem;
  background: #ffffff;
  color: #3b82f6;
  font-weight: 600;
  font-size: 1.125rem;
  border-radius: 50px;
  text-decoration: none;
  transition: all 0.3s ease;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}

.cta-button:hover {
  transform: translateY(-4px);
  box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
}

/* Responsive */
@media (max-width: 768px) {
  .hero-title {
    font-size: 2.5rem;
  }
  
  .hero-subtitle {
    font-size: 1.125rem;
  }
  
  .section-title {
    font-size: 2rem;
  }
  
  .mission-grid,
  .features-grid,
  .values-grid {
    grid-template-columns: 1fr;
  }
  
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 2rem;
  }
  
  .stat-number {
    font-size: 3rem;
  }
}
</style>

