<template>
  <div class="admin-wrapper">
    <!-- SIDEBAR (mini collapse) -->
    <aside class="sidebar" :class="{ collapsed: isCollapsed }">
      <div class="sidebar-header">
        <!-- nút 3 gạch + chữ DKMN -->
        <button class="menu-toggle" @click="isCollapsed = !isCollapsed" aria-label="Thu gọn/mở rộng sidebar">
          <i class="fas fa-bars"></i>
        </button>
        <span class="brand-text">DKMN</span>
      </div>

      <div class="sidebar-user">
        <i class="fas fa-user-circle user-icon"></i>
        <div class="user-text">
          <strong>ADMIN</strong><br />
          <small>Quản trị viên</small>
        </div>
      </div>

      <nav class="menu">
        <RouterLink to="/view/home">
          <i class="fas fa-home me-2"></i><span>Bảng điều khiển</span>
        </RouterLink>
        <RouterLink to="/view/chuyendi">
          <i class="fas fa-bus me-2"></i><span>Quản lý chuyến đi</span>
        </RouterLink>
        <RouterLink to="/view/donhang">
          <i class="fas fa-box me-2"></i><span>Quản lý đơn hàng</span>
        </RouterLink>
        <RouterLink to="/view/nguoidung">
          <i class="fas fa-users me-2"></i><span>Quản lý người dùng</span>
        </RouterLink>
        <RouterLink to="/view/thanhtoan">
          <i class="fas fa-credit-card me-2"></i><span>Quản lý thanh toán</span>
        </RouterLink>
        <RouterLink to="/view/danhgia">
          <i class="fas fa-star me-2"></i><span>Quản lý đánh giá</span>
        </RouterLink>
        <RouterLink to="/view/baocao">
          <i class="fas fa-chart-line me-2"></i><span>Báo cáo & Thống kê</span>
        </RouterLink>
      </nav>
    </aside>

    <!-- MAIN AREA -->
    <div class="main-area">
      <!-- TOPBAR -->
      <header class="topbar">
       
        <div class="right">
          <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Tìm kiếm nhanh..." />
          </div>
          <div class="topbar-icons">
            <i class="fas fa-bell"></i>
            <i class="fas fa-cog"></i>
          </div>
          <div class="avatar">
            <img src="https://cdn-icons-png.flaticon.com/512/149/149071.png" alt="admin avatar" />
          </div>
        </div>
      </header>

      <main class="content">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
const isCollapsed = ref(false) // true = mini (icon-only)
</script>

<style scoped>
.admin-wrapper {
  display: flex;
  height: 100vh;
  background: #f6f8fb;
  overflow: hidden;
}

/* ===== Sidebar ===== */
.sidebar {
  width: 250px;
  background-color: #0b3b6e;
  color: white;
  display: flex;
  flex-direction: column;
  padding: 20px 0;
  transition: width .25s ease, padding .25s ease;
}

/* mini mode */
.sidebar.collapsed {
  width: 64px;
}

/* header trong sidebar */
.sidebar-header {
  display: flex;
  align-items: center;
  gap: 10px;
  font-weight: 700;
  padding: 0 16px 16px;
  border-bottom: 1px solid rgba(255,255,255,.15);
}
.sidebar .menu-toggle {
  background: transparent;
  border: none;
  color: #fff;
  font-size: 18px;
  cursor: pointer;
}
.sidebar .brand-text { font-size: 1rem; }

/* user box */
.sidebar-user {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 16px 20px;
  border-bottom: 1px solid rgba(255,255,255,.15);
}
.user-icon { font-size: 28px; }
.sidebar-user small { color: #cdd4e1; }

/* menu */
.menu { display: flex; flex-direction: column; margin-top: 10px; }
.menu a {
  display: flex; align-items: center; gap: 12px;
  color: #cfd8e3; text-decoration: none;
  padding: 12px 16px; font-weight: 500; transition: .2s;
}
.menu a i { width: 24px; text-align: center; font-size: 18px; }
.menu a:hover, .menu a.router-link-active { background-color: #163f7a; color: #fff; }

/* Ẩn chữ khi mini */
.sidebar.collapsed .brand-text,
.sidebar.collapsed .user-text,
.sidebar.collapsed .menu a span {
  display: none;
}
.sidebar.collapsed .sidebar-user {
  justify-content: center;
}
.sidebar.collapsed .menu a {
  justify-content: center;
  padding: 12px 0;
}

/* ===== Main Area ===== */
.main-area { flex: 1; display: flex; flex-direction: column; overflow-y: auto; }

/* ===== Topbar ===== */
.topbar {
  background-color: #0b3b6e; color: white;
  padding: 12px 24px; display: flex; justify-content: flex-end; align-items: center;
  position: sticky; top: 0; z-index: 10;
}
.left { display: flex; align-items: center; gap: 12px; }
.topbar .menu-toggle { background: transparent; border: none; color: #fff; font-size: 20px; cursor: pointer; }
.topbar h2 { font-weight: 700; color: #fff; font-size: 1.3rem; }

.topbar .right { display: flex; align-items: center; gap: 18px; }

/* search */
.search-box { position: relative; }

.search-box input {
  padding: 6px 28px 6px 10px;
  border: 1px solid rgba(255,255,255,.35);
  border-radius: 20px; outline: none; transition: .2s;
  font-size: .9rem; background: rgba(255,255,255,.15); color: #fff;
}
.search-box input::placeholder { color: rgba(255,255,255,.8); }
.search-box i { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); color: #fff; font-size: .9rem; }

.topbar-icons i { font-size: 1.1rem; color: #fff; margin: 0 6px; cursor: pointer; transition: .2s; }
.topbar-icons i:hover { color: #e6f0ff; }

/* avatar */
.avatar img { width: 36px; height: 36px; border-radius: 50%; border: 2px solid #e6f0ff; cursor: pointer; transition: .2s; }
.avatar img:hover { transform: scale(1.05); }

/* small screens: giữ mini luôn nếu muốn, có thể set mặc định isCollapsed=true theo ý */
@media (max-width: 991px) {
  .sidebar { position: sticky; left: 0; top: 0; height: 100vh; }
}

</style>

