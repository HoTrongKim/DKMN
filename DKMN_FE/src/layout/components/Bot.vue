<template>
  <footer id="contact" class="bot-home mt-5">
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
  background: linear-gradient(120deg, #111a2d, #1f2961 55%, #312e81);
  color: #f3f6ff;
  padding: 2.5rem 1.5rem;
  border-top: 1px solid rgba(148, 163, 184, 0.25);
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
  color: #c7d2fe;
  text-decoration: none;
  font-weight: 600;
  font-size: 0.92rem;
  transition: color 0.2s ease, transform 0.2s ease;
}

.bot-home__links a:hover {
  color: #fef3c7;
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
