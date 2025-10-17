<template>
  <div class="quan-ly-nguoi-dung card p-4 shadow-sm">
    <!-- Tiêu đề -->
    <div class="header-bar d-flex align-items-center justify-content-between mb-3">
      <h4 class="page-title m-0 text-dark">
        <i class="fas fa-users me-2"></i> Quản Lý Người Dùng
      </h4>
      <button class="btn btn-success">
        <i class="fas fa-sync-alt me-1"></i> Tải lại
      </button>
    </div>

    <!-- Bộ lọc -->
    <div class="row g-2 mb-3">
      <div class="col-md-5">
        <div class="input-group">
          <span class="input-group-text"><i class="fas fa-search"></i></span>
          <input
            type="text"
            class="form-control"
            placeholder="Tìm theo tên, email, SĐT..."
          />
        </div>
      </div>
      <div class="col-md-3">
        <select class="form-select">
          <option value="">-- Tất cả trạng thái --</option>
          <option value="Hoạt động">Hoạt động</option>
          <option value="Bị khóa">Bị khóa</option>
        </select>
      </div>
    </div>

    <!-- Bảng danh sách -->
    <div class="table-responsive">
      <table class="table table-hover align-middle">
        <thead class="table-light border-bottom">
          <tr>
            <th>#</th>
            <th>Họ Tên</th>
            <th>Email</th>
            <th>SĐT</th>
            <th>Vai trò</th>
            <th>Trạng thái</th>
            <th class="text-center">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <!-- Dòng trống mặc định -->
          <tr>
            <td colspan="7" class="text-center text-muted py-4">
              <div class="mb-2"><i class="far fa-folder-open fa-lg"></i></div>
              Chưa có dữ liệu hiển thị
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal chi tiết người dùng -->
    <div class="modal-backdrop fade show" v-if="false"></div>
    <div class="modal d-block" v-if="false" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content shadow">
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title">
              <i class="fas fa-user-circle me-2"></i> Thông Tin Người Dùng
            </h5>
            <button type="button" class="btn-close btn-close-white"></button>
          </div>
          <div class="modal-body">
            <p><strong>Họ tên:</strong> Nguyễn Văn A</p>
            <p><strong>Email:</strong> a@gmail.com</p>
            <p><strong>SĐT:</strong> 0901234567</p>
            <p><strong>Vai trò:</strong> Khách hàng</p>
            <p><strong>Trạng thái:</strong> Hoạt động</p>
            <p><strong>Ngày đăng ký:</strong> 2024-05-12</p>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary">Đóng</button>
            <button class="btn btn-warning">
              <i class="fas fa-edit"></i> Sửa (demo)
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
// 👉 Không có logic hay API, chỉ là giao diện tĩnh
</script>

<style scoped>
.page-title {
  color: #000; /* 👈 Chuyển về màu đen */
  font-weight: 700;
}

.btn-success {
  background-color: #0b3b6e !important;
  border-color: #0b3b6e !important;
}
.btn-success:hover {
  background-color: #072e56 !important;
}

/* Modal style */
.modal-backdrop {
  position: fixed;
  inset: 0;
  background-color: rgba(0,0,0,0.4);
  z-index: 1050;
}
.modal.d-block {
  position: fixed;
  inset: 0;
  z-index: 1060;
  display: block;
}
.modal-content {
  border: none;
  border-radius: 10px;
}
</style>
