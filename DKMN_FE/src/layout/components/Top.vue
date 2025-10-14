<template>
  <header
    class="topbar d-flex align-items-center justify-content-between px-4 py-2"
  >
    <!-- Left: Logo + tagline -->
    <div class="d-flex align-items-center gap-3">
      <a
        href="/"
        class="d-flex align-items-center text-white text-decoration-none"
      >
        <img src="./src/assets/images/logo.png" class="logo me-2" />

        <h5 class="m-0 fw-bold text-white">DKMN</h5>
      </a>
	  
      <p class="tagline m-0 small text-white-50">
        Cam kết hoàn <strong>150%</strong> nếu nhà xe không cung cấp dịch vụ vận
        chuyển (*)
      </p>
    </div>

    <!-- Right: Login + Register -->
    <nav class="d-flex align-items-center gap-2">
      <button class="btn btn-login">Đăng nhập</button>
      <button class="btn btn-login">Đăng ký</button>
    </nav>
  </header>
</template>
  
  <script>
export default {
  name: "Top",
  methods: {},
};
</script>
  
  <style scoped>
.topbar {
  background-color: #0056d2;
  color: white;
  height: 56px;
}

/* .tagline {
  border-left: 1px solid rgba(255, 255, 255, 0.3);
  padding-left: 10px;
} */



.btn-login {
  background-color: white;
  color: #0056d2;
  border: none;
  font-weight: 600;
  font-size: 0.85rem;
  border-radius: 8px;
  padding: 4px 14px;
  transition: 0.3s;
}
.btn-login:hover {
  background-color: #f5f5f5;
}
.dropdown-menu {
  font-size: 0.85rem;
}
</style>
  