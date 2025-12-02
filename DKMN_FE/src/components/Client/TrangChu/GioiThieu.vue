<template>
  <section class="showcase" @mousemove="onMouseMove" ref="sectionRef">
    <!-- Canvas Background -->
    <canvas ref="canvasRef" class="bg-canvas"></canvas>
    
    <!-- Morphing Blobs -->
    <div class="blob b1"></div>
    <div class="blob b2"></div>
    <div class="blob b3"></div>
    
    <!-- 3D Grid -->
    <div class="grid-3d"></div>
    
    <!-- Light Beams -->
    <div class="beams">
      <div class="beam" v-for="i in 5" :key="i"></div>
    </div>
    
    <!-- Particles -->
    <div class="particles">
      <span v-for="n in 40" :key="n" class="dot" :style="particleStyle(n)"></span>
    </div>

    <div class="main-content">
      <!-- Left Side -->
      <div class="left">
        <div class="badge-wrap">
          <div class="badge">
            <span class="spark">✨</span>
            <span>Nền tảng đặt vé #1 Việt Nam</span>
          </div>
        </div>

        <h1 class="hero-title">
          <span class="line">Khám phá hành trình</span>
          <span class="line highlight">
            <span class="glow-text">Thông Minh</span>
          </span>
          <span class="line">cùng chúng tôi</span>
        </h1>

        <p class="hero-desc">
          Trải nghiệm đặt vé hiện đại với AI, so sánh giá thông minh 
          và thanh toán an toàn tuyệt đối.
        </p>

        <div class="features">
          <div class="feat" v-for="(f, i) in feats" :key="i" :style="{'--i': i}">
            <div class="feat-icon">{{ f.icon }}</div>
            <div class="feat-info">
              <strong>{{ f.title }}</strong>
              <span>{{ f.desc }}</span>
            </div>
          </div>
        </div>

        <div class="btns">
          <button class="btn-main">
            <span>Khám phá ngay</span>
            <svg viewBox="0 0 24 24" width="20" height="20"><path d="M9 5l7 7-7 7" stroke="currentColor" stroke-width="2" fill="none"/></svg>
          </button>
          <button class="btn-alt">
            <svg viewBox="0 0 24 24" width="20" height="20"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none"/><path d="M10 8l6 4-6 4z" fill="currentColor"/></svg>
            <span>Xem video</span>
          </button>
        </div>

        <div class="stats-row">
          <div class="stat" v-for="(s, i) in stats" :key="i">
            <div class="stat-num">{{ s.num }}</div>
            <div class="stat-lbl">{{ s.lbl }}</div>
          </div>
        </div>
      </div>

      <!-- Right Side - 3D Visual -->
      <div class="right">
        <div class="scene" :style="sceneStyle">
          <!-- Main Ticket -->
          <div class="ticket-3d">
            <div class="ticket-glow"></div>
            <div class="ticket-inner">
              <div class="t-head">
                <div class="t-logo">✈️</div>
                <span class="t-type">E-TICKET</span>
              </div>
              <div class="t-route">
                <div class="t-city">
                  <strong>HCM</strong>
                  <small>Tân Sơn Nhất</small>
                </div>
                <div class="t-line">
                  <div class="plane-fly">✈</div>
                  <div class="dash"></div>
                </div>
                <div class="t-city">
                  <strong>HN</strong>
                  <small>Nội Bài</small>
                </div>
              </div>
              <div class="t-info">
                <div><small>Khởi hành</small><strong>{{ time }}</strong></div>
                <div><small>Thời gian</small><strong>2h 15m</strong></div>
                <div><small>Hạng</small><strong>Business</strong></div>
              </div>
              <div class="t-barcode">
                <span v-for="i in 12" :key="i" :style="{height: 15 + Math.random()*25 + 'px'}"></span>
              </div>
            </div>
          </div>

          <!-- Floating Cards -->
          <div class="float-card fc1" :style="fc1Style">
            <span class="fc-icon">🎫</span>
            <div><small>Giảm giá</small><strong>20%</strong></div>
          </div>
          <div class="float-card fc2" :style="fc2Style">
            <span class="fc-icon">⚡</span>
            <div><small>Đặt vé</small><strong>30s</strong></div>
          </div>
          <div class="float-card fc3" :style="fc3Style">
            <span class="fc-icon">🛡️</span>
            <div><small>Bảo mật</small><strong>100%</strong></div>
          </div>

          <!-- Orbits -->
          <div class="orbit o1"><div class="orb-dot"></div></div>
          <div class="orbit o2"><div class="orb-dot"></div><div class="orb-dot d2"></div></div>
          <div class="orbit o3"><div class="orb-dot"></div></div>

          <!-- Energy Rings -->
          <div class="energy-ring" v-for="i in 3" :key="'e'+i"></div>
        </div>
      </div>
    </div>

    <!-- Cursor Effect -->
    <div class="cursor-glow" :style="cursorStyle"></div>
  </section>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'

const sectionRef = ref(null)
const canvasRef = ref(null)
const mx = ref(0)
const my = ref(0)
const time = ref('07:30')

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

const sceneStyle = computed(() => ({
  transform: `perspective(1200px) rotateY(${mx.value * 0.02}deg) rotateX(${-my.value * 0.02}deg)`
}))

const cursorStyle = computed(() => ({
  left: mx.value + 'px',
  top: my.value + 'px'
}))

const fc1Style = computed(() => ({
  transform: `translate(${mx.value * 0.03}px, ${my.value * 0.02}px)`
}))
const fc2Style = computed(() => ({
  transform: `translate(${mx.value * -0.02}px, ${my.value * 0.03}px)`
}))
const fc3Style = computed(() => ({
  transform: `translate(${mx.value * 0.02}px, ${my.value * -0.02}px)`
}))

function onMouseMove(e) {
  const rect = e.currentTarget.getBoundingClientRect()
  mx.value = e.clientX - rect.left - rect.width / 2
  my.value = e.clientY - rect.top - rect.height / 2
}

function particleStyle(n) {
  return {
    left: Math.random() * 100 + '%',
    '--d': Math.random() * 20 + 10 + 's',
    '--dl': Math.random() * 10 + 's',
    '--sz': 2 + Math.random() * 4 + 'px'
  }
}

let animId = null
function initCanvas() {
  const c = canvasRef.value
  if (!c) return
  const ctx = c.getContext('2d')
  c.width = window.innerWidth
  c.height = window.innerHeight
  
  const pts = Array.from({length: 60}, () => ({
    x: Math.random() * c.width,
    y: Math.random() * c.height,
    vx: (Math.random() - 0.5) * 0.8,
    vy: (Math.random() - 0.5) * 0.8
  }))
  
  function draw() {
    ctx.clearRect(0, 0, c.width, c.height)
    pts.forEach((p, i) => {
      p.x += p.vx; p.y += p.vy
      if (p.x < 0 || p.x > c.width) p.vx *= -1
      if (p.y < 0 || p.y > c.height) p.vy *= -1
      
      pts.forEach((q, j) => {
        if (i < j) {
          const d = Math.hypot(p.x - q.x, p.y - q.y)
          if (d < 120) {
            ctx.beginPath()
            ctx.strokeStyle = `rgba(34,211,238,${0.15 * (1 - d/120)})`
            ctx.moveTo(p.x, p.y)
            ctx.lineTo(q.x, q.y)
            ctx.stroke()
          }
        }
      })
      ctx.beginPath()
      ctx.fillStyle = 'rgba(34,211,238,0.6)'
      ctx.arc(p.x, p.y, 2, 0, Math.PI * 2)
      ctx.fill()
    })
    animId = requestAnimationFrame(draw)
  }
  draw()
}

onMounted(() => {
  initCanvas()
  setInterval(() => {
    const d = new Date()
    time.value = d.toLocaleTimeString('vi-VN', {hour:'2-digit', minute:'2-digit'})
  }, 1000)
  window.addEventListener('resize', initCanvas)
})

onUnmounted(() => {
  cancelAnimationFrame(animId)
  window.removeEventListener('resize', initCanvas)
})
</script>


<style scoped>
/* === BASE === */
.showcase {
  position: relative;
  min-height: 750px;
  padding: 60px 40px;
  overflow: hidden;
  background: linear-gradient(135deg, #041327 0%, #061a32 50%, #04152e 100%);
  color: #fff;
}

.bg-canvas {
  position: absolute;
  inset: 0;
  z-index: 1;
  opacity: 0.6;
}

/* === BLOBS === */
.blob {
  position: absolute;
  border-radius: 50%;
  filter: blur(100px);
  opacity: 0.5;
  animation: blobMove 20s ease-in-out infinite;
}
.b1 { width: 500px; height: 500px; background: #0ea5e9; top: -150px; left: -100px; }
.b2 { width: 400px; height: 400px; background: #22d3ee; bottom: -100px; right: -100px; animation-delay: -7s; }
.b3 { width: 350px; height: 350px; background: #5eead4; top: 50%; left: 40%; animation-delay: -14s; }

@keyframes blobMove {
  0%, 100% { transform: translate(0, 0) scale(1); }
  33% { transform: translate(60px, -40px) scale(1.1); }
  66% { transform: translate(-40px, 60px) scale(0.9); }
}

/* === 3D GRID === */
.grid-3d {
  position: absolute;
  inset: 0;
  background: 
    linear-gradient(rgba(255,255,255,0.03) 1px, transparent 1px),
    linear-gradient(90deg, rgba(255,255,255,0.03) 1px, transparent 1px);
  background-size: 60px 60px;
  transform: perspective(500px) rotateX(60deg);
  transform-origin: center top;
  animation: gridScroll 15s linear infinite;
}
@keyframes gridScroll {
  0% { background-position: 0 0; }
  100% { background-position: 0 60px; }
}

/* === BEAMS === */
.beams { position: absolute; inset: 0; overflow: hidden; pointer-events: none; }
.beam {
  position: absolute;
  width: 2px;
  height: 200px;
  background: linear-gradient(to bottom, transparent, rgba(34,211,238,0.6), transparent);
  animation: beamFall 4s linear infinite;
}
.beam:nth-child(1) { left: 10%; animation-delay: 0s; }
.beam:nth-child(2) { left: 30%; animation-delay: 0.8s; }
.beam:nth-child(3) { left: 50%; animation-delay: 1.6s; }
.beam:nth-child(4) { left: 70%; animation-delay: 2.4s; }
.beam:nth-child(5) { left: 90%; animation-delay: 3.2s; }

@keyframes beamFall {
  0% { transform: translateY(-200px); opacity: 0; }
  20% { opacity: 1; }
  80% { opacity: 1; }
  100% { transform: translateY(100vh); opacity: 0; }
}


/* === PARTICLES === */
.particles { position: absolute; inset: 0; pointer-events: none; z-index: 2; }
.dot {
  position: absolute;
  width: var(--sz);
  height: var(--sz);
  background: rgba(255,255,255,0.7);
  border-radius: 50%;
  animation: dotFloat var(--d) linear infinite;
  animation-delay: var(--dl);
  box-shadow: 0 0 6px rgba(34,211,238,0.8);
}
@keyframes dotFloat {
  0% { transform: translateY(100vh) scale(0); opacity: 0; }
  10% { opacity: 1; }
  90% { opacity: 1; }
  100% { transform: translateY(-50px) scale(1); opacity: 0; }
}

/* === MAIN CONTENT === */
.main-content {
  position: relative;
  z-index: 10;
  max-width: 1400px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 60px;
  align-items: center;
}

/* === LEFT SIDE === */
.left { animation: fadeUp 1s ease-out; }
@keyframes fadeUp {
  from { opacity: 0; transform: translateY(40px); }
  to { opacity: 1; transform: translateY(0); }
}

.badge-wrap { margin-bottom: 24px; }
.badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 20px;
  background: rgba(255,255,255,0.08);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255,255,255,0.15);
  border-radius: 50px;
  font-size: 14px;
  animation: badgePulse 2s ease-in-out infinite;
}
.spark { animation: spin 2s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
@keyframes badgePulse {
  0%, 100% { box-shadow: 0 0 0 0 rgba(34,211,238,0.4); }
  50% { box-shadow: 0 0 0 15px rgba(34,211,238,0); }
}

.hero-title {
  font-size: 52px;
  font-weight: 800;
  line-height: 1.15;
  margin-bottom: 20px;
}
.hero-title .line { display: block; }
.hero-title .line:nth-child(1) { animation: slideIn 0.8s ease-out 0.1s backwards; }
.hero-title .line:nth-child(2) { animation: slideIn 0.8s ease-out 0.3s backwards; }
.hero-title .line:nth-child(3) { animation: slideIn 0.8s ease-out 0.5s backwards; }
@keyframes slideIn {
  from { opacity: 0; transform: translateX(-30px); }
  to { opacity: 1; transform: translateX(0); }
}

.glow-text {
  background: linear-gradient(90deg, #7ce7ff, #22d3ee, #5eead4, #7ce7ff);
  background-size: 300% 100%;
  -webkit-background-clip: text;
  background-clip: text;
  -webkit-text-fill-color: transparent;
  animation: gradientMove 4s linear infinite;
  position: relative;
}
.glow-text::after {
  content: 'Thông Minh';
  position: absolute;
  left: 0;
  top: 0;
  background: inherit;
  -webkit-background-clip: text;
  background-clip: text;
  -webkit-text-fill-color: transparent;
  filter: blur(20px);
  opacity: 0.6;
  z-index: -1;
}
@keyframes gradientMove {
  0% { background-position: 0% 50%; }
  100% { background-position: 300% 50%; }
}

.hero-desc {
  font-size: 18px;
  color: rgba(255,255,255,0.75);
  line-height: 1.7;
  margin-bottom: 32px;
  max-width: 480px;
}


/* === FEATURES === */
.features {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 14px;
  margin-bottom: 32px;
}
.feat {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 16px;
  background: rgba(255,255,255,0.05);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255,255,255,0.1);
  border-radius: 16px;
  transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
  animation: featIn 0.6s ease-out calc(var(--i) * 0.1s) backwards;
}
@keyframes featIn {
  from { opacity: 0; transform: translateY(20px) scale(0.9); }
  to { opacity: 1; transform: translateY(0) scale(1); }
}
.feat:hover {
  background: rgba(255,255,255,0.1);
  border-color: rgba(34,211,238,0.5);
  transform: translateY(-6px) scale(1.02);
  box-shadow: 0 20px 40px rgba(0,0,0,0.3), 0 0 30px rgba(34,211,238,0.2);
}
.feat-icon {
  width: 44px;
  height: 44px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, rgba(14,165,233,0.3), rgba(34,211,238,0.3));
  border-radius: 12px;
  font-size: 22px;
  transition: transform 0.3s;
}
.feat:hover .feat-icon { transform: scale(1.15) rotate(10deg); }
.feat-info strong { display: block; font-size: 15px; margin-bottom: 2px; }
.feat-info span { font-size: 12px; color: rgba(255,255,255,0.6); }

/* === BUTTONS === */
.btns { display: flex; gap: 16px; margin-bottom: 40px; }
.btn-main, .btn-alt {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 14px 28px;
  border: none;
  border-radius: 14px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}
.btn-main {
  background: linear-gradient(120deg, #0ea5e9, #22d3ee);
  color: #0b172a;
  box-shadow: 0 4px 20px rgba(34,211,238,0.5);
  position: relative;
  overflow: hidden;
}
.btn-main::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
  animation: btnShine 3s infinite;
}
@keyframes btnShine {
  0% { left: -100%; }
  50%, 100% { left: 100%; }
}
.btn-main:hover {
  transform: translateY(-3px) scale(1.02);
  box-shadow: 0 8px 30px rgba(34,211,238,0.6);
}
.btn-main svg { transition: transform 0.3s; }
.btn-main:hover svg { transform: translateX(4px); }

.btn-alt {
  background: rgba(255,255,255,0.08);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255,255,255,0.2);
  color: #fff;
}
.btn-alt:hover {
  background: rgba(255,255,255,0.15);
  transform: translateY(-3px);
}
.btn-alt svg { animation: playPulse 1.5s ease-in-out infinite; }
@keyframes playPulse {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.1); }
}

/* === STATS === */
.stats-row { display: flex; gap: 40px; }
.stat { text-align: center; }
.stat-num {
  font-size: 36px;
  font-weight: 700;
  background: linear-gradient(135deg, #22d3ee, #5eead4);
  -webkit-background-clip: text;
  background-clip: text;
  -webkit-text-fill-color: transparent;
  animation: countUp 1s ease-out;
}
@keyframes countUp {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
.stat-lbl { font-size: 13px; color: rgba(255,255,255,0.6); margin-top: 4px; }


/* === RIGHT SIDE - 3D SCENE === */
.right { perspective: 1500px; }
.scene {
  position: relative;
  width: 100%;
  height: 550px;
  transform-style: preserve-3d;
  transition: transform 0.15s ease-out;
}

/* === TICKET 3D === */
.ticket-3d {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%) rotateY(-5deg);
  width: 340px;
  transform-style: preserve-3d;
  animation: ticketFloat 5s ease-in-out infinite;
}
@keyframes ticketFloat {
  0%, 100% { transform: translate(-50%, -50%) rotateY(-5deg) translateZ(0); }
  50% { transform: translate(-50%, -50%) rotateY(5deg) translateZ(30px); }
}

.ticket-glow {
  position: absolute;
  inset: -20px;
  background: linear-gradient(135deg, #0ea5e9, #22d3ee, #5eead4);
  border-radius: 30px;
  filter: blur(40px);
  opacity: 0.5;
  animation: glowPulse 3s ease-in-out infinite;
}
@keyframes glowPulse {
  0%, 100% { opacity: 0.5; transform: scale(1); }
  50% { opacity: 0.7; transform: scale(1.05); }
}

.ticket-inner {
  background: linear-gradient(145deg, rgba(255,255,255,0.95), rgba(240,249,255,0.95));
  backdrop-filter: blur(20px);
  border-radius: 24px;
  padding: 24px;
  box-shadow: 
    0 25px 50px rgba(0,0,0,0.3),
    0 0 0 1px rgba(255,255,255,0.1),
    inset 0 1px 0 rgba(255,255,255,0.5);
  color: #1e293b;
  position: relative;
  overflow: hidden;
}
.ticket-inner::before {
  content: '';
  position: absolute;
  top: -50%;
  left: -50%;
  width: 200%;
  height: 200%;
  background: linear-gradient(45deg, transparent 40%, rgba(255,255,255,0.8) 50%, transparent 60%);
  animation: ticketShine 4s infinite;
}
@keyframes ticketShine {
  0% { transform: translateX(-100%) rotate(45deg); }
  100% { transform: translateX(100%) rotate(45deg); }
}

.t-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  padding-bottom: 16px;
  border-bottom: 2px dashed rgba(0,0,0,0.1);
}
.t-logo {
  width: 50px;
  height: 50px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #dbeafe, #e0e7ff);
  border-radius: 14px;
  font-size: 28px;
  animation: logoSpin 10s linear infinite;
}
@keyframes logoSpin {
  0% { transform: rotateY(0deg); }
  100% { transform: rotateY(360deg); }
}
.t-type {
  font-size: 11px;
  font-weight: 700;
  color: #64748b;
  letter-spacing: 2px;
}

.t-route {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 20px;
}
.t-city { text-align: center; }
.t-city strong { display: block; font-size: 32px; color: #0f172a; }
.t-city small { font-size: 11px; color: #64748b; }

.t-line {
  flex: 1;
  margin: 0 16px;
  position: relative;
  height: 30px;
}
.dash {
  position: absolute;
  top: 50%;
  left: 0;
  right: 0;
  height: 2px;
  background: repeating-linear-gradient(90deg, #cbd5e1 0, #cbd5e1 8px, transparent 8px, transparent 16px);
}
.plane-fly {
  position: absolute;
  top: 50%;
  left: 0;
  transform: translateY(-50%);
  font-size: 18px;
  animation: planeFly 3s ease-in-out infinite;
}
@keyframes planeFly {
  0%, 100% { left: 0; }
  50% { left: calc(100% - 20px); }
}

.t-info {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 12px;
  padding: 14px;
  background: rgba(34,211,238,0.08);
  border-radius: 12px;
  margin-bottom: 16px;
}
.t-info > div { text-align: center; }
.t-info small { display: block; font-size: 10px; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; }
.t-info strong { font-size: 15px; color: #0f172a; }

.t-barcode {
  display: flex;
  gap: 3px;
  justify-content: center;
  padding-top: 14px;
  border-top: 2px dashed rgba(0,0,0,0.1);
}
.t-barcode span {
  width: 4px;
  background: #0f172a;
  border-radius: 2px;
  animation: barcodeWave 2s ease-in-out infinite;
  animation-delay: calc(var(--i, 0) * 0.1s);
}
.t-barcode span:nth-child(odd) { animation-delay: 0.1s; }
.t-barcode span:nth-child(even) { animation-delay: 0.2s; }
@keyframes barcodeWave {
  0%, 100% { transform: scaleY(1); }
  50% { transform: scaleY(0.7); }
}


/* === FLOATING CARDS === */
.float-card {
  position: absolute;
  transition: transform 0.2s ease-out;
  animation: floatCard 6s ease-in-out infinite;
}
.fc1 { top: 5%; left: 0; animation-delay: 0s; }
.fc2 { top: 55%; right: -5%; animation-delay: -2s; }
.fc3 { bottom: 10%; left: 5%; animation-delay: -4s; }

@keyframes floatCard {
  0%, 100% { transform: translateY(0) rotate(0deg); }
  25% { transform: translateY(-15px) rotate(3deg); }
  75% { transform: translateY(10px) rotate(-3deg); }
}

.float-card .card-glow {
  position: absolute;
  inset: -10px;
  background: linear-gradient(135deg, #0ea5e9, #22d3ee);
  border-radius: 20px;
  filter: blur(20px);
  opacity: 0.4;
}

.float-card > div:last-child {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 14px 18px;
  background: rgba(255,255,255,0.95);
  backdrop-filter: blur(10px);
  border-radius: 16px;
  box-shadow: 0 10px 30px rgba(0,0,0,0.2);
  position: relative;
}
.fc-icon { font-size: 28px; }
.float-card small { display: block; font-size: 11px; color: #64748b; }
.float-card strong { font-size: 18px; color: #0f172a; font-weight: 700; }

/* === ORBITS === */
.orbit {
  position: absolute;
  top: 50%;
  left: 50%;
  border: 1px solid rgba(255,255,255,0.1);
  border-radius: 50%;
  transform: translate(-50%, -50%);
}
.o1 { width: 380px; height: 380px; animation: orbitSpin 20s linear infinite; }
.o2 { width: 480px; height: 480px; animation: orbitSpin 30s linear infinite reverse; }
.o3 { width: 580px; height: 580px; animation: orbitSpin 40s linear infinite; }

@keyframes orbitSpin { to { transform: translate(-50%, -50%) rotate(360deg); } }

.orb-dot {
  position: absolute;
  width: 10px;
  height: 10px;
  background: linear-gradient(135deg, #22d3ee, #5eead4);
  border-radius: 50%;
  top: 0;
  left: 50%;
  transform: translateX(-50%);
  box-shadow: 0 0 15px rgba(34,211,238,0.8);
}
.orb-dot.d2 { top: auto; bottom: 0; }

/* === ENERGY RINGS === */
.energy-ring {
  position: absolute;
  top: 50%;
  left: 50%;
  width: 300px;
  height: 300px;
  border: 2px solid rgba(34,211,238,0.3);
  border-radius: 50%;
  transform: translate(-50%, -50%);
  animation: energyPulse 3s ease-out infinite;
}
.energy-ring:nth-child(2) { animation-delay: 1s; }
.energy-ring:nth-child(3) { animation-delay: 2s; }

@keyframes energyPulse {
  0% { transform: translate(-50%, -50%) scale(0.8); opacity: 1; }
  100% { transform: translate(-50%, -50%) scale(2); opacity: 0; }
}

/* === CURSOR GLOW === */
.cursor-glow {
  position: fixed;
  width: 300px;
  height: 300px;
  background: radial-gradient(circle, rgba(34,211,238,0.15), transparent 70%);
  border-radius: 50%;
  pointer-events: none;
  transform: translate(-50%, -50%);
  z-index: 100;
  transition: opacity 0.3s;
}

/* === RESPONSIVE === */
@media (max-width: 1024px) {
  .main-content { grid-template-columns: 1fr; text-align: center; }
  .left { order: 1; }
  .right { order: 2; height: 450px; }
  .hero-title { font-size: 40px; }
  .features { justify-content: center; }
  .btns { justify-content: center; }
  .stats-row { justify-content: center; }
  .hero-desc { margin: 0 auto 32px; }
}

@media (max-width: 768px) {
  .showcase { padding: 40px 20px; }
  .hero-title { font-size: 32px; }
  .features { grid-template-columns: 1fr; }
  .btns { flex-direction: column; }
  .btn-main, .btn-alt { width: 100%; justify-content: center; }
  .ticket-3d { width: 280px; }
  .float-card { display: none; }
}
</style>
