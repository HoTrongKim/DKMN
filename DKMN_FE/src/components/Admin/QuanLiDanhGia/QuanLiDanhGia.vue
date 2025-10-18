<template>
  <div class="quan-ly-danh-gia card p-4 shadow-sm">
    <!-- Tiêu đề -->
    <div class="header-bar d-flex align-items-center justify-content-between mb-3">
      <h4 class="page-title m-0">
        <i class="fas fa-star-half-alt me-2"></i> Quản Lý Đánh Giá & Phản Hồi
      </h4>
      <div class="d-flex gap-2 align-items-center">
        <div class="action-buttons me-2" role="group" aria-label="Actions">
          <button class="btn btn-approve" @click="approveSelected">
            <i class="fas fa-check me-1"></i> Duyệt
          </button>
          <button class="btn btn-hide" @click="hideSelected">
            <i class="fas fa-eye-slash me-1"></i> Ẩn
          </button>
          <button class="btn btn-delete" @click="deleteSelected">
            <i class="fas fa-trash-alt me-1"></i> Xóa
          </button>
        </div>
        <button class="btn btn-primary" @click="fetchReviews">
          <i class="fas fa-sync-alt me-1"></i> Tải lại
        </button>
      </div>
    </div>

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
              <th style="width:40px"><input type="checkbox" v-model="selectAll" @change="toggleSelectAll" /></th>
              <th>ID</th>
              <th>Điểm</th>
              <th>Nội dung</th>
              <th>Khách hàng</th>
              <th>Chuyến đi</th>
              <th>Trạng thái</th>
            </tr>
          </thead>
          <tbody>
            <!-- Hàng trống mặc định -->
            <tr v-if="reviews.length === 0">
              <td colspan="7" class="text-center text-muted py-4">
                <i class="far fa-folder-open fa-lg mb-2"></i><br />
                Chưa có đánh giá nào được hiển thị.
              </td>
            </tr>
            <!-- Example rows (placeholder) -->
            <tr v-for="rev in reviews" :key="rev.id">
              <td><input type="checkbox" v-model="selected" :value="rev.id" /></td>
              <td>{{ rev.id }}</td>
              <td>{{ rev.rating }}</td>
              <td>{{ rev.content }}</td>
              <td>{{ rev.customer }}</td>
              <td>{{ rev.trip }}</td>
              <td>{{ rev.status }}</td>
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

// Selection & data placeholders
const reviews = ref([
  // Example placeholder entries; replace with real API data
  // { id: 1, rating: 5, content: 'Tuyệt vời', customer: 'Nguyễn A', trip: 'TP-HN', status: 'Hiển thị' }
]);
const selected = ref([]);
const selectAll = ref(false);

const fetchReviews = () => {
  console.log("Đang tải danh sách đánh giá... (FE placeholder)");
  // TODO: fetch real data and populate `reviews`
};

const toggleSelectAll = () => {
  if (selectAll.value) {
    selected.value = reviews.value.map(r => r.id);
  } else {
    selected.value = [];
  }
};

const approveSelected = () => {
  if (!selected.value.length) return alert('Vui lòng chọn ít nhất 1 mục để duyệt.');
  console.log('Approve IDs:', selected.value);
  // TODO: call API to approve; then refresh list
};

const hideSelected = () => {
  if (!selected.value.length) return alert('Vui lòng chọn ít nhất 1 mục để ẩn.');
  console.log('Hide IDs:', selected.value);
  // TODO: call API to hide; then refresh list
};

const deleteSelected = () => {
  if (!selected.value.length) return alert('Vui lòng chọn ít nhất 1 mục để xóa.');
  if (!confirm('Bạn có chắc muốn xóa các mục đã chọn?')) return;
  console.log('Delete IDs:', selected.value);
  // TODO: call API to delete; then refresh list
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

/* New action buttons in header */
.header-bar .btn-group .btn {
  min-width: 72px;
}

/* Action buttons container - more spacing and harmonious colors */
.action-buttons {
  display: flex;
  gap: 10px;
  align-items: center;
}
.action-buttons .btn {
  min-width: 86px;
  padding: 6px 12px;
  border-radius: 8px;
  color: #fff;
  box-shadow: 0 2px 6px rgba(16,24,40,0.08);
  transition: transform .12s ease, box-shadow .12s ease;
}
.action-buttons .btn:active { transform: translateY(1px); }

.btn-approve { background: linear-gradient(90deg,#2EC4B6,#1FA3A0); border: none; }
.btn-approve:hover { box-shadow: 0 6px 18px rgba(46,196,182,0.18); }
.btn-hide { background: linear-gradient(90deg,#FFB86B,#FF9E49); border: none; color:#111; }
.btn-hide:hover { box-shadow: 0 6px 18px rgba(255,184,107,0.18); }
.btn-delete { background: linear-gradient(90deg,#FF6B6B,#FF4C4C); border: none; }
.btn-delete:hover { box-shadow: 0 6px 18px rgba(255,107,107,0.18); }

/* Checkbox column sizing */
table th[style], table td:first-child { text-align: center; }
</style>
