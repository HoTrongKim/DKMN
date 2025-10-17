<template>
  <nav class="menu">
    <a class="menu-item" @click.prevent="$router.push('/view')">
      <i class="fas fa-chart-pie"></i><span>Bảng điều Khiển</span>
    </a>

    <a class="menu-item" @click.prevent="$router.push('/view/chuyendi')">
      <i class="fas fa-route"></i><span>Quản lý chuyến đi</span>
    </a>

    <a class="menu-item" @click.prevent="$router.push('/view/donhang')">
      <i class="fas fa-ticket-alt"></i><span>Quản lý đơn hàng</span>
    </a>

    <a class="menu-item" @click.prevent="$router.push('/view/nguoidung')">
      <i class="fas fa-users-cog"></i><span>Quản lý người dùng</span>
    </a>

    <a class="menu-item" @click.prevent="$router.push('/view/thanhtoan')">
      <i class="fas fa-wallet"></i><span>Quản lý thanh toán</span>
    </a>

    <a class="menu-item" @click.prevent="$router.push('/view/danhgia')">
      <i class="fas fa-star"></i><span>Quản lý đánh giá & phản hồi</span>
    </a>

    <a class="menu-item" @click.prevent="$router.push('/view/baocao')">
      <i class="fas fa-chart-line"></i><span>Báo cáo & thống kê</span>
    </a>
  </nav>
</template>

<script setup>
// KHAI BÁO PROP collapsed
defineProps({
    collapsed: {
        type: Boolean,
        default: false,
    },
});
</script>

<style scoped>
/* KHÔNG CẦN DÙNG PROP collapsed VÌ CSS TRONG ADMINSHELL ĐÃ XỬ LÝ ẨN TEXT */
.menu {
  padding: 10px;
}
.menu-item {
  display: flex;
  align-items: center;
  gap: 10px;
  color: #c9d6ee;
  text-decoration: none;
  border-radius: 8px;
  padding: 10px 12px;
  font-size: 0.92rem;
  cursor: pointer;
  transition: background 0.15s ease, color 0.15s ease;
}
.menu-item:hover {
  background: #1b3a6b;
  color: #fff;
}
.menu-item i {
  width: 20px;
  text-align: center;
  font-size: 16px;
  flex: 0 0 20px;
}
/* QUAN TRỌNG: Loại bỏ CSS ẩn text cũ. CSS ẩn text MỚI đã chuyển sang AdminShell.vue */
.menu-item span {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
</style>