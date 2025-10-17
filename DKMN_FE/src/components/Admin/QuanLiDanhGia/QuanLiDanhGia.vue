<template>
  <div class="quan-ly-danh-gia card p-4 shadow-sm">
   

    <!-- Bộ lọc -->
    <div class="row g-2 mb-3">
      <div class="col-md-4">
        <select class="form-select" v-model="filterRating">
          <option value="">-- Lọc theo Điểm sao --</option>
          <option value="5">5 Sao</option>
          <option value="4">4 Sao</option>
          <option value="3">3 Sao</option>
          <option value="2">2 Sao</option>
          <option value="1">1 Sao</option>
        </select>
      </div>
      <div class="col-md-4">
        <select class="form-select" v-model="filterStatus">
          <option value="">-- Lọc theo Trạng thái --</option>
          <option value="Hiển thị">Hiển thị</option>
          <option value="Đã ẩn">Đã ẩn</option>
          <option value="Chờ duyệt">Chờ duyệt</option>
        </select>
      </div>
    </div>

    <!-- Bảng -->
    <div class="table-responsive">
      <table class="table table-hover align-middle">
        <thead class="table-light border-bottom">
          <tr>
            <th>ID</th>
            <th>Điểm</th>
            <th>Nội dung</th>
            <th>Khách hàng</th>
            <th>Chuyến đi</th>
            <th>Trạng thái</th>
            <th class="text-center">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <!-- Hàng trống mặc định -->
          <tr>
            <td colspan="7" class="text-center text-muted py-4">
              <i class="far fa-folder-open fa-lg mb-2"></i><br />
              Chưa có đánh giá nào được hiển thị.
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Ghi chú nghiệp vụ -->
    <div class="alert alert-info mt-3 mb-0">
      <ul class="mb-0">
        <li>Xem danh sách các phản hồi, đánh giá từ khách hàng.</li>
        <li>Duyệt, ẩn hoặc xóa các nhận xét vi phạm.</li>
        <li>Tổng hợp thống kê mức độ hài lòng (trung bình sao, số lượng phản hồi).</li>
      </ul>
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue";

const filterRating = ref("");
const filterStatus = ref("");

const fetchReviews = () => {
  console.log("Đang tải danh sách đánh giá... (FE placeholder)");
};
</script>

<style scoped>
:root {
  --dk-blue: #0b3b6e;
  --dk-blue-600: #0a3563;
}

/* Tiêu đề */
.page-title {
  color: var(--dk-blue);
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 8px;
}
.header-bar {
  border-bottom: 2px solid #eef2f6;
  padding-bottom: 10px;
}

/* Nút */
.btn-primary {
  background: var(--dk-blue);
  border-color: var(--dk-blue);
}
.btn-primary:hover {
  background: var(--dk-blue-600);
  border-color: var(--dk-blue-600);
}

/* Bảng */
.table thead th {
  color: var(--dk-blue);
  font-weight: 600;
  white-space: nowrap;
}
.table tbody td {
  vertical-align: middle;
}
</style>
