<template>
  <section class="intro-section">
    <div class="container">
      <div class="row align-items-center">
        <!-- Left Side: Text Content -->
        <div class="col-lg-6 mb-4 mb-lg-0">
          <div class="badge-pill mb-3">
             <span class="badge-icon">✨</span> Nền tảng đặt vé #1 Việt Nam
          </div>
          
          <h1 class="intro-title">
            Khám phá hành trình <span class="highlight">Thông Minh</span> <br>
            cùng chúng tôi
          </h1>

          <p class="intro-desc">
            Trải nghiệm đặt vé hiện đại với công nghệ mới, so sánh giá minh bạch 
            và thanh toán an toàn tuyệt đối.
          </p>

          <div class="features-grid">
            <div class="feature-item" v-for="(f, i) in feats" :key="i">
              <div class="feature-icon">{{ f.icon }}</div>
              <div class="feature-text">
                <strong>{{ f.title }}</strong>
                <span>{{ f.desc }}</span>
              </div>
            </div>
          </div>

          <div class="action-buttons">
            <button class="btn-primary-custom">
              Khám phá ngay <i class='bx bx-right-arrow-alt'></i>
            </button>
            <button class="btn-outline-custom">
              <i class='bx bx-play-circle'></i> Xem video
            </button>
          </div>
          
          <div class="stats-row mt-5">
             <div class="stat-item" v-for="(s, i) in stats" :key="i">
               <div class="stat-num">{{ s.num }}</div>
               <div class="stat-lbl">{{ s.lbl }}</div>
             </div>
          </div>
        </div>

        <!-- Right Side: Static Image (Replacing 3D Scene) -->
        <div class="col-lg-6 text-center">
          <div class="intro-image-container">
             <!-- Placeholder for a nice vector illustration or image -->
             <img src="https://f.vexere.com/images/banner/banner-home-01.png" alt="Intro" class="img-fluid rounded-4 shadow-sm"
                  @error="e => e.target.src = 'https://via.placeholder.com/600x400?text=DKMN+Intro'" />
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
const feats = [
  { icon: '⚡', title: 'So sánh giá', desc: 'Tìm giá tốt nhất' },
  { icon: '🧭', title: 'AI thông minh', desc: 'Gợi ý lộ trình tối ưu' },
  { icon: '🛡️', title: 'An toàn', desc: 'Bảo mật đa lớp' },
  { icon: '🎯', title: 'Ưu đãi', desc: 'Giảm giá độc quyền' }
]

const stats = [
  { num: '50K+', lbl: 'Khách hàng' },
  { num: '100+', lbl: 'Tuyến đường' },
  { num: '4.9★', lbl: 'Đánh giá' }
]
</script>

<style scoped>
.intro-section {
  padding: 80px 0;
  background: #ffffff;
  color: #334155;
  overflow: hidden;
}

.badge-pill {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
  background: #eff6ff;
  color: #2563eb;
  border-radius: 50px;
  font-size: 14px;
  font-weight: 600;
  border: 1px solid #dbeafe;
}

.intro-title {
  font-size: 42px;
  font-weight: 800;
  color: #0f172a;
  line-height: 1.2;
  margin-bottom: 24px;
}

.highlight {
  color: #2563eb;
}

.intro-desc {
  font-size: 18px;
  color: #64748b;
  margin-bottom: 32px;
  max-width: 500px;
  line-height: 1.6;
}

.features-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
  margin-bottom: 40px;
}

.feature-item {
  display: flex;
  align-items: flex-start;
  gap: 12px;
}

.feature-icon {
  width: 40px;
  height: 40px;
  background: #f0f9ff;
  color: #0ea5e9;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
}

.feature-text strong {
  display: block;
  color: #0f172a;
  font-size: 16px;
}

.feature-text span {
  font-size: 13px;
  color: #64748b;
}

.action-buttons {
  display: flex;
  gap: 16px;
}

.btn-primary-custom {
  background: #2563eb;
  color: #fff;
  border: none;
  padding: 12px 28px;
  border-radius: 8px;
  font-weight: 600;
  font-size: 16px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.btn-primary-custom:hover {
  background: #1d4ed8;
}

.btn-outline-custom {
  background: transparent;
  color: #2563eb;
  border: 1px solid #2563eb;
  padding: 12px 28px;
  border-radius: 8px;
  font-weight: 600;
  font-size: 16px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.btn-outline-custom:hover {
  background: #eff6ff;
}

.stats-row {
  display: flex;
  gap: 40px;
}

.stat-num {
  font-size: 32px;
  font-weight: 700;
  color: #2563eb;
}

.stat-lbl {
  font-size: 14px;
  color: #64748b;
}

.intro-image-container img {
  border: 4px solid #fff;
  box-shadow: 0 20px 40px rgba(0,0,0,0.1);
}
</style>
