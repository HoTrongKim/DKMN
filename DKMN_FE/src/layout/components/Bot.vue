<template>
  <footer id="contact" class="bot-home ">
    <div class="bot-home__container">
      <span>© 2025 DKMN. All rights reserved.</span>
      <div class="bot-home__links">
        <a href="#">Chính sách bảo mật</a>
        <a href="#">Điều khoản dịch vụ</a>
        <a href="#">Liên hệ</a>
      </div>
    </div>
  </footer>
</template>
<script>
export default {};
</script>
<style scoped>
.bot-home {
  background: radial-gradient(circle at 18% 12%, rgba(37, 99, 235, 0.2), transparent 40%),
    radial-gradient(circle at 82% 18%, rgba(14, 165, 233, 0.16), transparent 36%),
    linear-gradient(135deg, #0b1224, #0f172a 48%, #0b1224);
  color: #e2e8f0;
  padding: 2.5rem 1.5rem;
  border-top: 1px solid rgba(96, 165, 250, 0.25);
  box-shadow: 0 -10px 30px rgba(5, 9, 20, 0.35);
}

.bot-home__container {
  width: min(1180px, 100%);
  margin: 0 auto;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1.25rem;
  flex-wrap: wrap;
  font-size: 0.95rem;
}

.bot-home__links {
  display: flex;
  gap: 1.5rem;
  flex-wrap: wrap;
}

.bot-home__links a {
  color: #cbd5e1;
  text-decoration: none;
  font-weight: 700;
  font-size: 0.92rem;
  transition: color 0.2s ease, transform 0.2s ease;
}

.bot-home__links a:hover {
  color: #fff;
  transform: translateY(-1px);
}

@media (max-width: 576px) {
  .bot-home__container {
    flex-direction: column;
    text-align: center;
  }

  .bot-home__links {
    justify-content: center;
  }
}
</style>
