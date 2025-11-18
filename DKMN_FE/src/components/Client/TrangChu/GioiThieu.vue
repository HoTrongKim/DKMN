<template>
  <section class="travel-showcase" @mousemove="trackMouse">
    
    <!-- Aurora Background -->
    <div class="aurora"></div>

    <!-- Floating Particles -->
    <span v-for="n in 40" :key="n" class="particle"></span>

    <div class="inner">
      <!-- LEFT CONTENT -->
      <div class="content">
        <h2 class="title">
          Trải nghiệm <span class="highlight">Du Lịch Thông Minh</span>  
          cùng nền tảng đặt vé hiện đại.
        </h2>

        <p class="desc">
          Tích hợp AI • Gợi ý hành trình tối ưu • Cập nhật giá theo thời gian thực.
        </p>

        <div class="features">
          <div class="feature-card card-1 tilt">
            <span class="icon">⚡</span>
            <p>So sánh giá tức thì</p>
          </div>

          <div class="feature-card card-2 tilt">
            <span class="icon">🧭</span>
            <p>Định tuyến thông minh</p>
          </div>

          <div class="feature-card card-3 tilt">
            <span class="icon">🛡️</span>
            <p>Thanh toán an toàn</p>
          </div>
        </div>
      </div>

      <!-- RIGHT VISUAL -->
      <div class="visual">

        <!-- Orbit circle -->
        <div class="orbit"></div>

        <!-- Main 3D Ticket -->
        <div class="ticket-3d tilt" :style="tiltStyle">
          <div class="ticket-header">Vé điện tử</div>
          <div class="ticket-body">
            <p>TP.HCM → Hà Nội</p>
            <small>Khởi hành: 07:30 • Hãng: Express</small>
          </div>
        </div>

        <!-- Icons orbiting -->
        <div class="orbit-icon icon-plane">✈️</div>
        <div class="orbit-icon icon-train">🚆</div>
        <div class="orbit-icon icon-bus">🚌</div>

        <!-- Light streaks -->
        <div class="light-streak"></div>
        <div class="light-streak delay"></div>

      </div>
    </div>
  </section>
</template>

<script setup>
import { ref } from "vue";

const tiltX = ref(0);
const tiltY = ref(0);

const tiltStyle = ref({});

function trackMouse(event) {
  const w = window.innerWidth;
  const h = window.innerHeight;

  tiltX.value = ((event.clientX - w / 2) / w) * 18;
  tiltY.value = ((event.clientY - h / 2) / h) * 18;

  tiltStyle.value = {
    transform: `rotateX(${tiltY.value}deg) rotateY(${tiltX.value}deg)`
  };
}
</script>

<style scoped>
/* ------------------------------------------------------
   SECTION BASE
------------------------------------------------------- */
.travel-showcase {
  position: relative;
  padding: 70px 40px;
  min-height: 500px;
  overflow: hidden;
  border-radius: 28px;
  background: #0a0f2b;
  box-shadow: 0 20px 60px rgba(0,0,0,0.6);
  color: #fff;
}

/* ------------------------------------------------------
   AURORA BACKGROUND
------------------------------------------------------- */
.aurora {
  position: absolute;
  inset: 0;
  background: radial-gradient(circle at 20% 50%, rgba(56,189,248,0.2), transparent 40%),
              radial-gradient(circle at 80% 60%, rgba(167,139,250,0.25), transparent 40%),
              radial-gradient(circle at 50% 10%, rgba(16,185,129,0.18), transparent 50%);
  filter: blur(60px);
  animation: auroraMove 10s infinite alternate ease-in-out;
}
@keyframes auroraMove {
  0% { transform: translateY(-20px); opacity: 0.75; }
  100% { transform: translateY(20px); opacity: 1; }
}

/* ------------------------------------------------------
   LAYOUT
------------------------------------------------------- */
.inner {
  display: grid;
  grid-template-columns: 1.2fr 1fr;
  gap: 40px;
  position: relative;
  z-index: 5;
  align-items: center;
}

.title {
  font-size: 32px;
  font-weight: 800;
  margin-bottom: 14px;
}
.highlight {
  background: linear-gradient(120deg, #60a5fa, #34d399);
  -webkit-background-clip: text;
  color: transparent;
}

.desc {
  opacity: 0.85;
  margin-bottom: 22px;
  font-size: 16px;
}

/* ------------------------------------------------------
   FEATURES – GLASS CARDS
------------------------------------------------------- */
.features {
  display: flex;
  gap: 16px;
  flex-wrap: wrap;
}
.feature-card {
  padding: 16px 20px;
  background: rgba(255,255,255,0.08);
  border: 1px solid rgba(255,255,255,0.15);
  border-radius: 14px;
  backdrop-filter: blur(12px);
  box-shadow: 0 10px 25px rgba(0,0,0,0.3);
  transition: 0.25s ease;
}
.feature-card:hover {
  transform: translateY(-4px) scale(1.03);
}
.icon { font-size: 20px; }

/* ------------------------------------------------------
   VISUAL SIDE
------------------------------------------------------- */
.visual {
  position: relative;
  min-height: 300px;
}

/* Orbit circle */
.orbit {
  position: absolute;
  inset: 0;
  width: 260px;
  height: 260px;
  margin: auto;
  border-radius: 50%;
  border: 1px solid rgba(255,255,255,0.15);
  animation: spin 14s linear infinite;
}
@keyframes spin {
  to { transform: rotate(360deg); }
}

/* 3D Ticket */
.ticket-3d {
  width: 220px;
  margin: auto;
  background: linear-gradient(135deg, rgba(255,255,255,0.15), rgba(99,102,241,0.2));
  backdrop-filter: blur(16px);
  border-radius: 18px;
  padding: 14px;
  border: 1px solid rgba(255,255,255,0.2);
  transform-style: preserve-3d;
  transition: transform .25s ease;
}
.ticket-header {
  font-weight: 700;
  margin-bottom: 8px;
}
.ticket-body small {
  opacity: .8;
}

/* Orbiting icons */
.orbit-icon {
  position: absolute;
  font-size: 36px;
  animation: floatOrbit 10s linear infinite;
}
.icon-plane { top: -8%; left: 42%; }
.icon-train { bottom: -6%; left: 10%; animation-duration: 12s; }
.icon-bus { top: 38%; right: -6%; animation-duration: 16s; }

@keyframes floatOrbit {
  0% { transform: rotate(0deg) translateX(100px) rotate(0deg); }
  100% { transform: rotate(360deg) translateX(100px) rotate(-360deg); }
}

/* Light streaks */
.light-streak {
  position: absolute;
  top: 50%;
  width: 160px;
  height: 4px;
  left: -160px;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,0.5), transparent);
  animation: streakMove 3s ease infinite;
}
.delay { animation-delay: 1.6s; }

@keyframes streakMove {
  0% { transform: translateX(0); opacity: 0; }
  20% { opacity: 1; }
  100% { transform: translateX(500px); opacity: 0; }
}

/* Particles */
.particle {
  position: absolute;
  width: 4px;
  height: 4px;
  background: rgba(255,255,255,0.55);
  border-radius: 50%;
  animation: drift 8s linear infinite;
}
@keyframes drift {
  from { transform: translateY(260px) scale(.3); opacity: .3; }
  to { transform: translateY(-140px) scale(1); opacity: .8; }
}
</style>
