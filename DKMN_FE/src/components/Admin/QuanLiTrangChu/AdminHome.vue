<template>
  <div class="admin-dashboard">
    <div class="card p-4 shadow-sm">
      <div class="header-bar d-flex align-items-center justify-content-between mb-3">
        <h4 class="page-title m-0 text-dark">
          <i class="fas fa-chart-line me-2"></i> Bảng điều khiển
        </h4>
        <div></div>
      </div>

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
// Hiển thị tĩnh - Sẵn sàng để thêm logic API
</script>

<style scoped>
/* Định nghĩa biến màu hiện đại và tối giản */
:root {
  --dk-primary: #007bff; 
  --dk-secondary: #6c757d; 
  --dk-light-bg: #fdfdfe; 
  --dk-gray-bg: #f9fbfd; 
  --dk-text-main: #212529; 
  --dk-border: #e9ecef;
}

/* KHUNG CHUNG */
.admin-dashboard {
  padding: 0;
  background: var(--dk-gray-bg);
  min-height: 100vh;
}

/* Thẻ bao ngoài */
.card {
  border: 1px solid var(--dk-border);
  border-radius: 16px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03);
  background: var(--dk-light-bg);
}

/* --- Header đồng bộ --- */
.header-bar {
  border-bottom: 1px solid var(--dk-border); 
  padding-bottom: 15px;
  margin-bottom: 25px !important; 
}
.page-title {
  color: var(--dk-text-main);
  font-weight: 700;
  font-size: 1.75rem; 
}
.page-title i { color: var(--dk-primary); }

/* --- Cards tổng quan (STATS) --- */
.stats-grid{
  display:grid; 
  grid-template-columns:repeat(auto-fit, minmax(220px,1fr));
  gap:24px; 
}
.stat-card{
  display:flex; align-items:center; 
  background:#fff;
  border-radius:12px; 
  padding:20px 24px;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.03); 
  border: 1px solid var(--dk-border);
  transition: box-shadow .2s ease, transform .2s ease;
}
.stat-card:hover{ 
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08); 
  transform: translateY(-3px); 
}

/* ICON BOX VÀ MÀU LOANG */
.icon-box{
  width:50px; height:50px; border-radius:12px; 
  display:grid; place-items:center;
  font-size:24px; 
  color:#fff; /* Icon màu trắng tương phản tốt trên nền gradient */
  flex-shrink:0;
  margin-right: 18px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); 
}

/* --- ĐỊNH NGHĨA MÀU LOANG CHO TỪNG CARD --- */

/* 1. Xanh Sáng (Vé bán) */
.stat-card.blue .icon-box{
    background: linear-gradient(135deg, #007bff, #00c6ff); 
    box-shadow: 0 4px 12px rgba(0, 123, 255, 0.4);
}
/* 2. Xanh Lục (Doanh thu) */
.stat-card.green .icon-box{
    background: linear-gradient(135deg, #28a745, #20c997);
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.4);
}
/* 3. Tím Đỏ (Khách mới) */
.stat-card.purple .icon-box{
    background: linear-gradient(135deg, #6f42c1, #e83e8c);
    box-shadow: 0 4px 12px rgba(111, 66, 193, 0.4);
}
/* 4. Vàng Cam (Đánh giá) */
.stat-card.orange .icon-box{
    background: linear-gradient(135deg, #fd7e14, #ffc107);
    box-shadow: 0 4px 12px rgba(253, 126, 20, 0.4);
}

/* Typography Info (Giá trị LỚN và ĐẬM) */
.info h5{
    font-size:.9rem; 
    font-weight:500; 
    margin-bottom: 4px;
    color: var(--dk-secondary); 
    text-transform: uppercase; 
    letter-spacing: .5px;
}
.info p{
    font-size:1.8rem; 
    font-weight: 800; 
    color: var(--dk-text-main);
    margin:0;
}
/* Đặt lại màu cho giá trị đặc biệt */
.stat-card.blue .info p { color: #007bff; }
.stat-card.green .info p { color: #28a745; }


/* --- Biểu đồ & hoạt động --- */
.dashboard-content{
  display:grid; grid-template-columns:2fr 1fr; 
  gap:24px; 
  margin-top:30px;
}
.chart-box, .activity-box{
  background:#fff; 
  border-radius:12px; 
  padding:20px; 
  box-shadow:0 2px 10px rgba(0,0,0,.03);
  border: 1px solid var(--dk-border);
}
.chart-box h5, .activity-box h5{
  color: var(--dk-text-main); 
  font-weight:600; 
  font-size: 1.1rem;
  padding-bottom: 0;
  margin-bottom: 15px;
}
.chart-box h5 i, .activity-box h5 i {
    color: var(--dk-primary);
}

.empty-box{
  border:2px dashed #d9e2f3; 
  border-radius:10px; 
  color:var(--dk-secondary); 
  text-align:center;
  padding:50px 0; 
  margin-top:12px; 
  font-style:italic;
  background: var(--dk-gray-bg); 
  transition: all 0.3s ease;
}
.empty-box:hover {
    border-color: #007bff;
    color: #007bff;
    background: #eef4f8; 
}

/* Responsive */
@media (max-width: 992px){
  .dashboard-content{ grid-template-columns:1fr; }
}
</style>