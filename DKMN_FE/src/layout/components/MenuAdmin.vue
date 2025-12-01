<template>
  <aside :class="['sidebar', { collapsed }]">
    <!-- User info + nút tròn ở góc phải -->
    <div class="user-info">
      <Icon icon="mdi:shield-account" width="20" />
      <div class="user-text" v-if="!collapsed">
  <p class="admin-title">ADMIN</p>
  <p class="admin-role">Quản trị viên</p>
</div>

      <!-- nút tròn 3 sọc -->
      <button class="fab-toggle" @click="$emit('toggle')" aria-label="Thu gọn/Mở rộng">
        <Icon icon="mdi:menu" width="16" />
      </button>
    </div>

    <!-- Menu -->
    <nav class="menu">
      <RouterLink
        to="/view/home"
        class="menu-item"
        active-class="active"
        :data-tip="collapsed ? 'Bảng điều khiển' : null"
      >
        <Icon icon="mdi:view-dashboard-outline" width="20" />
        <span v-if="!collapsed">Bảng điều khiển</span>
      </RouterLink>

      <RouterLink
        to="/view/chuyendi"
        class="menu-item"
        active-class="active"
        :data-tip="collapsed ? 'Quản lý chuyến đi' : null"
      >
        <Icon icon="mdi:bus" width="20" />
        <span v-if="!collapsed">Quản lý chuyến đi</span>
      </RouterLink>

      <RouterLink
        to="/view/donhang"
        class="menu-item"
        active-class="active"
        :data-tip="collapsed ? 'Quản lý đơn hàng' : null"
      >
        <Icon icon="mdi:package-variant-closed" width="20" />
        <span v-if="!collapsed">Quản lý đơn hàng</span>
      </RouterLink>

      <RouterLink
        to="/view/nguoidung"
        class="menu-item"
        active-class="active"
        :data-tip="collapsed ? 'Quản lý người dùng' : null"
      >
        <Icon icon="mdi:account-group" width="20" />
        <span v-if="!collapsed">Quản lý người dùng</span>
      </RouterLink>

      <RouterLink
        to="/view/thanhtoan"
        class="menu-item"
        active-class="active"
        :data-tip="collapsed ? 'Quản lý thanh toán' : null"
      >
        <Icon icon="mdi:credit-card-outline" width="20" />
        <span v-if="!collapsed">Quản lý thanh toán</span>
      </RouterLink>

      <RouterLink
        to="/view/danhgia"
        class="menu-item"
        active-class="active"
        :data-tip="collapsed ? 'Quản lý đánh giá' : null"
      >
        <Icon icon="mdi:star-outline" width="20" />
        <span v-if="!collapsed">Quản lý đánh giá</span>
      </RouterLink>

      <RouterLink
        to="/view/baocao"
        class="menu-item"
        active-class="active"
        :data-tip="collapsed ? 'Báo cáo & Thống kê' : null"
      >
        <Icon icon="mdi:chart-line" width="20" />
        <span v-if="!collapsed">Báo cáo & Thống kê</span>
      </RouterLink>

      <RouterLink
        to="/view/lienhe"
        class="menu-item"
        active-class="active"
        :data-tip="collapsed ? 'Quản lý liên hệ' : null"
      >
        <Icon icon="mdi:email-outline" width="20" />
        <span v-if="!collapsed">Quản lý liên hệ</span>
      </RouterLink>
    </nav>
  </aside>
</template>

<script setup>
import { Icon } from '@iconify/vue'
defineProps({
  collapsed: { type: Boolean, default: false }
})
</script>

<style scoped>
/* ========================== */
/* 🎨 SIDEBAR – PHIÊN BẢN ĐẸP HƠN */
/* ========================== */
.user-text {
  display: flex;
  flex-direction: column;
  line-height: 1.3;
  margin-left: 4px;
}

.admin-title {
  font-weight: 600;
  font-size: 0.95rem;
  letter-spacing: 0.4px;
  color: #fff;
  margin: 0;
}

.admin-role {
  font-weight: 400;
  font-size: 0.8rem;
  color: #c9d6f0;
  margin: 0;
  opacity: 0.9;
}

.sidebar {
  position: relative;
  width: 100%;
  height: 100%;
  background: linear-gradient(180deg, #0b3b6e 0%, #105099 100%);
  color: #fff;
  display: flex;
  flex-direction: column;
  transition: width 0.25s ease;
  box-shadow: 2px 0 10px rgba(0, 0, 0, 0.15);
  overflow: hidden;
  font-family: 'Poppins', 'Inter', sans-serif;
}

/* Width được điều khiển bởi parent layout */


/* ========== USER INFO ========== */
.user-info {
  position: relative;
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 14px 14px 14px 20px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.15);
  color: #e5eaf5;
  font-family: 'Poppins', 'Inter', sans-serif;
  font-weight: 500;
}

.user-text strong {
  font-size: 0.9rem;
  font-weight: 600;
  letter-spacing: 0.3px;
}

.user-text small {
  font-size: 0.78rem;
  font-weight: 400;
  opacity: 0.9;
}

/* ========== NÚT TRÒN MENU ========== */
.fab-toggle {
  margin-left: auto;
  position: static;
  width: 28px;
  height: 28px;
  border-radius: 999px;
  border: none;
  display: grid;
  place-items: center;
  cursor: pointer;
  background: linear-gradient(135deg, #60a5fa, #2563eb);
  color: #fff;
  box-shadow: 0 6px 14px rgba(2, 6, 23, 0.24);
  transition: filter 0.15s, transform 0.15s;
}

.fab-toggle:hover {
  filter: brightness(1.05);
  transform: translateY(-1px) scale(1.03);
}

/* Khi thu gọn */
.sidebar.collapsed .user-info {
  justify-content: center;
  padding: 16px 0;
}

.sidebar.collapsed .fab-toggle {
  position: absolute;
  top: 50%;
  right: -14px; /* cắt nửa nút */
  transform: translateY(-50%);
  margin: 0;
}

/* ========== MENU CHÍNH ========== */
.menu {
  flex: 1;
  display: flex;
  flex-direction: column;
  padding: 10px 8px;
  font-family: 'Poppins', 'Inter', sans-serif;
}

.menu-item {
  position: relative;
  display: flex;
  align-items: center;
  gap: 10px;
  color: rgba(255, 255, 255, 0.85);
  padding: 10px 16px;
  border-radius: 10px;
  text-decoration: none;
  margin: 4px 8px;
  font-weight: 500;
  letter-spacing: 0.2px;
  transition: all 0.25s ease;
}

.menu-item:hover {
  background: rgba(255, 255, 255, 0.15);
  color: #fff;
  transform: translateX(3px);
}

.menu-item.active {
  background: linear-gradient(90deg, #2563eb 0%, #1e3a8a 100%);
  color: #fff;
  box-shadow: 0 2px 8px rgba(37, 99, 235, 0.4);
  font-weight: 600;
}

.menu-item :deep(svg) {
  color: #93c5fd;
  transition: color 0.2s;
}

.menu-item:hover :deep(svg),
.menu-item.active :deep(svg) {
  color: #fff;
}

/* ========== THU GỌN ========== */
.sidebar.collapsed .menu-item {
  justify-content: center;
  padding: 10px 0;
}

.sidebar.collapsed .menu-item span {
  display: none;
}

/* Tooltip khi thu gọn */
.sidebar.collapsed .menu-item[data-tip]:hover::after {
  content: attr(data-tip);
  position: absolute;
  left: calc(100% + 8px);
  top: 50%;
  transform: translateY(-50%);
  white-space: nowrap;
  background: #0b3b6e;
  color: #fff;
  padding: 6px 10px;
  border-radius: 8px;
  font-size: 0.85rem;
  font-family: 'Poppins', 'Inter', sans-serif;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.18);
}

.sidebar.collapsed .menu-item[data-tip]:hover::before {
  content: '';
  position: absolute;
  left: 100%;
  top: 50%;
  transform: translateY(-50%) rotate(45deg);
  width: 10px;
  height: 10px;
  background: #0b3b6e;
}

/* Width được điều khiển bởi parent layout */

/* === user row === */
.user-info{
  position: relative;             /* để đặt nút khi thu gọn */
  display:flex; align-items:center; gap:10px;
  padding:14px 14px 14px 20px;
  border-bottom:1px solid rgba(255,255,255,.15);
  color:#e5eaf5;
}
.user-text strong{ font-size:.9rem; }
.user-text small{ font-size:.78rem; opacity:.8; }

/* nút tròn – trạng thái MỞ: nằm gọn trong hàng, không cắt */
.fab-toggle{
  margin-left:auto;
  position: static;               /* nằm trong flow bình thường */
  width:28px; height:28px;
  border-radius:999px; border:none;
  display:grid; place-items:center; cursor:pointer;
  background: linear-gradient(135deg,#60a5fa,#2563eb);
  color:#fff;
  box-shadow: 0 6px 14px rgba(2,6,23,.24);
  transition: filter .15s, transform .15s;
}
.fab-toggle:hover{ filter:brightness(1.05); transform: translateY(-1px) scale(1.02); }

/* Khi THU GỌN: dock ra mép, bị cắt nửa */
.sidebar.collapsed .user-info{ justify-content:center; padding:16px 0; }
.sidebar.collapsed .fab-toggle{
  position:absolute;
  top:50%;
  right:-14px;                    /* nửa kích thước nút (28/2) -> bị cắt 1/2 */
  transform:translateY(-50%);
  margin:0;
}

/* menu */
.menu{ flex:1; display:flex; flex-direction:column; padding:10px 8px; }
.menu-item{
  position:relative;
  display:flex; align-items:center; gap:10px;
  color:#cfd8e3; padding:10px 16px; border-radius:10px;
  text-decoration:none; margin:4px 8px; font-weight:500;
  transition:all .25s ease;
}
.menu-item:hover{ background:rgba(255,255,255,.15); color:#fff; transform: translateX(3px); }
.menu-item.active{
  background:linear-gradient(90deg,#2563eb 0%,#1e3a8a 100%);
  color:#fff; box-shadow:0 2px 8px rgba(37,99,235,.4);
}
.menu-item :deep(svg){ color:#93c5fd; transition: color .2s; }
.menu-item:hover :deep(svg){ color:#fff; }

/* thu gọn */
.sidebar.collapsed .menu-item{ justify-content:center; padding:10px 0; }
.sidebar.collapsed .menu-item span{ display:none; }

/* tooltip khi thu gọn */
.sidebar.collapsed .menu-item[data-tip]:hover::after{
  content: attr(data-tip);
  position:absolute; left: calc(100% + 8px); top:50%; transform:translateY(-50%);
  white-space:nowrap; background:#0b3b6e; color:#fff;
  padding:6px 10px; border-radius:8px; font-size:.85rem;
  box-shadow:0 8px 20px rgba(0,0,0,.18);
}
.sidebar.collapsed .menu-item[data-tip]:hover::before{
  content:''; position:absolute; left:100%; top:50%;
  transform: translateY(-50%) rotate(45deg);
  width:10px; height:10px; background:#0b3b6e;
}
</style>
