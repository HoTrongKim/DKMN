<template>
    <!-- Features Section -->
    <section id="features" class="features-section py-5">
      <div class="container">
        <div class="row text-center mb-5">
          <div class="col-12">
            <h2 class="section-title">Tại sao chọn DKMN?</h2>
            <p class="section-subtitle">
              Những lý do khiến DKMN trở thành lựa chọn hàng đầu
            </p>
          </div>
        </div>
  
        <div class="row g-4">
          <div class="col-md-4">
            <div class="feature-card">
              <div class="feature-icon">
                <i class="bx bx-search-alt"></i>
              </div>
              <h4>Tìm kiếm thông minh</h4>
              <p>Tìm chuyến đi phù hợp với nhiều bộ lọc và tùy chọn sắp xếp</p>
            </div>
          </div>
  
          <div class="col-md-4">
            <div class="feature-card">
              <div class="feature-icon">
                <i class="bx bx-shield-check"></i>
              </div>
              <h4>Đặt vé an toàn</h4>
              <p>Hệ thống thanh toán bảo mật và chính sách hủy/đổi linh hoạt</p>
            </div>
          </div>
  
          <div class="col-md-4">
            <div class="feature-card">
              <div class="feature-icon">
                <i class="bx bx-support"></i>
              </div>
              <h4>Hỗ trợ 24/7</h4>
              <p>Đội ngũ chăm sóc khách hàng luôn sẵn sàng hỗ trợ bạn</p>
            </div>
          </div>
        </div>
      </div>
    </section>
  </template>
  
  <script>
  export default {
    name: "FeaturesSection",
  };
  </script>
  
  <style scoped>
  .features-section {
    background-color: #f9fafc;
  }
  
  .section-title {
    font-weight: 700;
    color: #0056d2;
  }
  
  .feature-card {
    background: #fff;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
  }
  
  .feature-card:hover {
    transform: translateY(-5px);
  }
  
  .feature-icon {
    font-size: 48px;
    color: #0056d2;
    margin-bottom: 15px;
  }
  </style>
  