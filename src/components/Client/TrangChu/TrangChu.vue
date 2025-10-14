<template>
    <header class="topbar d-flex align-items-center justify-content-between px-4 py-2">
      <!-- Left: Logo + tagline -->
      <div class="d-flex align-items-center gap-3">
        <a href="/" class="d-flex align-items-center text-white text-decoration-none">
          <img src="/src/assets/images/logo.png" alt="DKMN logo" class="logo me-2" />
          <h5 class="m-0 fw-bold text-white">DKMN</h5>
        </a>
        <p class="tagline m-0 small text-white-50">
          Cam kết hoàn <strong>150%</strong> nếu nhà xe không cung cấp dịch vụ vận chuyển (*)
        </p>
      </div>
  
      <!-- Right: nav -->
      <nav class="d-flex align-items-center gap-3">
        <a href="#" class="nav-link text-white text-decoration-none small">Đơn hàng của tôi</a>
        <a href="#" class="nav-link text-white text-decoration-none small">Mở bán vé trên DKMN</a>
        <div class="dropdown">
          <a
            href="#"
            class="nav-link text-white text-decoration-none small dropdown-toggle"
            data-bs-toggle="dropdown"
          >
            Trở thành đối tác
          </a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="#">Nhà xe</a></li>
            <li><a class="dropdown-item" href="#">Đại lý</a></li>
          </ul>
        </div>
  
        <!-- Language -->
        <img src="/src/assets/images/icons/flag-en.png" alt="English" class="flag" />
  
        <!-- Hotline -->
        <button class="btn btn-hotline d-flex align-items-center gap-1">
          <i class="bi bi-telephone-fill"></i>
          <span>Hotline 24/7</span>
        </button>
  
        <!-- Login -->
        <button class="btn btn-login">Đăng nhập</button>
      </nav>
    </header>
  </template>
  
  <script>
  export default {
    name: "Top",
    methods: {
      callHotline() {
        window.location.href = "tel:19001000";
      },
    },
  };
  </script>
  
  <style scoped>
  .topbar {
    background-color: #0056d2;
    color: white;
    height: 56px;
  }
  .logo {
    height: 30px;
    width: auto;
  }
  .tagline {
    border-left: 1px solid rgba(255, 255, 255, 0.3);
    padding-left: 10px;
  }
  .flag {
    width: 24px;
    height: 16px;
    border-radius: 2px;
  }
  .btn-hotline {
    background-color: white;
    color: #0056d2;
    border: none;
    font-weight: 600;
    font-size: 0.85rem;
    border-radius: 8px;
    padding: 4px 10px;
    transition: 0.3s;
  }
  .btn-hotline:hover {
    background-color: #f5f5f5;
  }
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
  