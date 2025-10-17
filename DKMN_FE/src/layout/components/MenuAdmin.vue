<template>
  
  <div class="menu-wrapper">
    <!-- 🔹 Nút mở menu -->
    <button class="menu-toggle" @click="toggleMenu">
      <i class="fas fa-bars"></i>
      <span>Menu</span>
    </button>

    <!-- 🔹 Thanh menu -->
    <nav v-show="isOpen" class="menu">
      <router-link class="menu-item" to="/view/home">
        <i class="fas fa-home"></i>
        <span>Bảng điều khiển</span>
      </router-link>

      <router-link class="menu-item" to="/view/chuyendi">
        <i class="fas fa-bus"></i>
        <span>Quản lý chuyến đi</span>
      </router-link>

      <router-link class="menu-item" to="/view/donhang">
        <i class="fas fa-box"></i>
        <span>Quản lý đơn hàng</span>
      </router-link>

      <router-link class="menu-item" to="/view/nguoidung">
        <i class="fas fa-users"></i>
        <span>Quản lý người dùng</span>
      </router-link>

      <router-link class="menu-item" to="/view/thanhtoan">
        <i class="fas fa-credit-card"></i>
        <span>Quản lý thanh toán</span>
      </router-link>

      <router-link class="menu-item" to="/view/danhgia">
        <i class="fas fa-star"></i>
        <span>Quản lý đánh giá</span>
      </router-link>

      <router-link class="menu-item" to="/view/baocao">
        <i class="fas fa-chart-line"></i>
        <span>Báo cáo & Thống kê</span>
      </router-link>
    </nav>
  </div>
</template>

<script>
export default {
  name: 'MenuAdmin',
  data() {
    return {
      isOpen: false
    };
  },
  methods: {
    toggleMenu() {
      this.isOpen = !this.isOpen;
    }
  }
};
</script>

<style scoped>
.menu-wrapper {
  position: relative;
  display: inline-block;
}

/* 🔹 Nút toggle */
.menu-toggle {
  display: flex;
  align-items: center;
  gap: 6px;
  background-color: #0056d2;
  color: #fff;
  border: none;
  border-radius: 8px;
  padding: 10px 14px;
  cursor: pointer;
  font-size: 1rem;
  transition: background 0.2s;
}

.menu-toggle:hover {
  background-color: #0040a8;
}

.menu-toggle i {
  font-size: 1.2rem;
}

/* 🔹 Menu */
.menu {
  position: absolute;
  top: 45px;
  left: 0;
  z-index: 999;
  padding: 10px;
  display: flex;
  flex-direction: column;
  gap: 6px;
  width: 220px;

  border: 1px solid #d0d7de;
  border-radius: 10px;
  background-color: #f8f9fb;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
}

/* 🔹 Mục menu */
.menu-item {
  display: flex;
  align-items: center;
  gap: 10px;
  color: #000;
  text-decoration: none;
  border-radius: 8px;
  padding: 10px 12px;
  font-size: 0.95rem;
  transition: background 0.15s ease, color 0.15s ease;
}

.menu-item:hover,
.menu-item.router-link-active {
  background: #163f7a;
  color: #fff;
}

.menu-item i {
  width: 20px;
  text-align: center;
}
</style>
