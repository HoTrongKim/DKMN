<template>
  <div class="admin-dashboard">
    <div class="card p-4 shadow-sm">
      <!-- Header chuẩn -->
      <div class="header-bar d-flex align-items-center justify-content-between mb-3">
        <h4 class="page-title m-0 text-dark">
          <i class="fas fa-chart-line me-2"></i> Bảng điều khiển
        </h4>
        <div></div>
      </div>

      <!-- Tổng quan -->
      <div class="stats-grid">
        <div class="stat-card blue">
          <div class="icon-box"><i class="fas fa-ticket-alt"></i></div>
          <div class="info"><h5>Vé bán hôm nay</h5><p>—</p></div>
        </div>
        <div class="stat-card green">
          <div class="icon-box"><i class="fas fa-sack-dollar"></i></div>
          <div class="info"><h5>Doanh thu</h5><p>—</p></div>
        </div>
        <div class="stat-card purple">
          <div class="icon-box"><i class="fas fa-user-plus"></i></div>
          <div class="info"><h5>Khách mới</h5><p>—</p></div>
        </div>
        <div class="stat-card orange">
          <div class="icon-box"><i class="fas fa-star"></i></div>
          <div class="info"><h5>Điểm đánh giá</h5><p>—</p></div>
        </div>
      </div>

      <!-- Biểu đồ / Hoạt động -->
      <div class="dashboard-content">
        <div class="chart-box">
          <h5><i class="fas fa-chart-bar me-2"></i> Doanh thu</h5>
          <div class="empty-box">Chưa có dữ liệu hiển thị</div>
        </div>
        <div class="activity-box">
          <h5><i class="fas fa-clock me-2"></i> Hoạt động gần đây</h5>
          <div class="empty-box">Chưa có dữ liệu hiển thị</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
// hiển thị tĩnh
</script>

<style scoped>
:root { --dk-blue:#0b3b6e; --dk-light:#eef2f6; }

/* KHÔNG cần padding riêng nữa vì đã có card p-4 */
.admin-dashboard { padding: 0; }

/* Header đồng bộ */
.header-bar {
  border-bottom: 2px solid var(--dk-light);
  padding-bottom: 10px;
}
.page-title{
  margin:0;
  color:#000;
  font-weight:700;
  font-size:1.5rem;
  line-height:1.2;
  display:flex; align-items:center; gap:8px;
}

/* Cards tổng quan */
.stats-grid{
  display:grid; grid-template-columns:repeat(auto-fit, minmax(240px,1fr));
  gap:20px; margin-top:16px;
}
.stat-card{
  display:flex; align-items:center; background:#fff;
  border-radius:12px; padding:16px; box-shadow:0 2px 6px rgba(0,0,0,.05);
  transition:transform .2s ease;
}
.stat-card:hover{ transform:translateY(-3px); }
.icon-box{
  width:48px;height:48px;border-radius:12px;display:grid;place-items:center;
  font-size:22px;color:#fff;flex-shrink:0;
}
.stat-card.blue .icon-box{background:#007bff;}
.stat-card.green .icon-box{background:#28a745;}
.stat-card.purple .icon-box{background:#6f42c1;}
.stat-card.orange .icon-box{background:#fd7e14;}
.info h5{font-size:.95rem;font-weight:600;margin-bottom:4px;color:#333;}
.info p{font-size:.9rem;color:#777;margin:0;}

/* Biểu đồ & hoạt động */
.dashboard-content{
  display:grid; grid-template-columns:2fr 1fr; gap:20px; margin-top:24px;
}
.chart-box, .activity-box{
  background:#fff; border-radius:12px; padding:16px; box-shadow:0 2px 6px rgba(0,0,0,.05);
}
.chart-box h5, .activity-box h5{
  color:#000; font-weight:600; display:flex; align-items:center;
}
.empty-box{
  border:2px dashed #d9e2f3; border-radius:10px; color:#95a0b6; text-align:center;
  padding:40px 0; margin-top:12px; font-style:italic;
}

@media (max-width: 992px){
  .dashboard-content{ grid-template-columns:1fr; }
}
</style>
