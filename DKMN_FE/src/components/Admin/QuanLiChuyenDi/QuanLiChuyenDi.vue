<template>
  <div class="quan-ly-chuyen-di-page">
    <div class="page-container">
      <div class="card p-4 shadow-sm">
        <!-- Header -->
        <div class="header-bar d-flex align-items-center justify-content-between mb-3">
          <h4 class="page-title m-0 text-dark">
            <i class="fas fa-route me-2"></i> Quản Lý Chuyến Đi
          </h4>

          <div class="d-flex gap-2">
            <button class="btn btn-success" @click="openCreate">
              <i class="fas fa-plus-circle me-1"></i> Thêm chuyến
            </button>
            <button class="btn btn-outline-primary" @click="toggleLoading">
              <i class="fas fa-rotate me-1"></i> Demo tải
            </button>
          </div>
        </div>

        <!-- Filters -->
        <div class="row g-2 mb-3">
          <div class="col-12 col-md-3">
            <input class="form-control form-control-sm" placeholder="Tìm theo tuyến / hãng / mã" />
          </div>
          <div class="col-6 col-md-2">
            <select class="form-select form-select-sm">
              <option>Tất cả loại</option>
              <option>Xe khách</option>
              <option>Tàu</option>
              <option>Máy bay</option>
            </select>
          </div>
          <div class="col-6 col-md-2">
            <select class="form-select form-select-sm">
              <option>Tất cả trạng thái</option>
              <option>Còn vé</option>
              <option>Hết vé</option>
              <option>Hủy chuyến</option>
            </select>
          </div>
          <div class="col-6 col-md-2">
            <input type="date" class="form-control form-control-sm" />
          </div>
          <div class="col-6 col-md-2">
            <input type="date" class="form-control form-control-sm" />
          </div>
          <div class="col-12 col-md-1 d-grid">
            <button class="btn btn-sm btn-primary">Lọc</button>
          </div>
        </div>

        <!-- Table -->
        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead class="table-light border-bottom">
              <tr>
                <th>#</th>
                <th>Loại</th>
                <th>Tuyến</th>
                <th>Khởi hành</th>
                <th>Giá (từ)</th>
                <th>Còn chỗ</th>
                <th>Trạng thái</th>
                <th class="text-end">Thao tác</th>
              </tr>
            </thead>

            <tbody>
              <tr v-if="loading" v-for="n in 5" :key="'s-'+n">
                <td><span class="skeleton w-40"></span></td>
                <td><span class="skeleton w-60"></span></td>
                <td><div class="skeleton w-100 mb-1"></div><div class="skeleton w-60"></div></td>
                <td><span class="skeleton w-80"></span></td>
                <td><span class="skeleton w-60"></span></td>
                <td><span class="skeleton w-50"></span></td>
                <td><span class="skeleton w-70"></span></td>
                <td class="text-end"><span class="skeleton w-60 me-2"></span><span class="skeleton w-60"></span></td>
              </tr>

              <tr v-if="!loading">
                <td colspan="8" class="text-center text-muted py-4">
                  <div class="mb-2"><i class="far fa-folder-open fa-lg"></i></div>
                  Chưa có dữ liệu hiển thị
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center">
          <small>Tổng: 0</small>
          <ul class="pagination mb-0">
            <li class="page-item disabled"><span class="page-link">«</span></li>
            <li class="page-item disabled"><span class="page-link">1/1</span></li>
            <li class="page-item disabled"><span class="page-link">»</span></li>
          </ul>
        </div>

        <!-- Modal -->
        <div v-if="showModal" class="modal-backdrop fade show"></div>
        <div v-if="showModal" class="modal d-block" tabindex="-1" @click.self="closeModal">
          <div class="modal-dialog">
            <form class="modal-content" @submit.prevent="closeModal">
              <div class="modal-header bg-warning-subtle">
                <h5 class="modal-title">{{ isEdit ? 'Chỉnh sửa chuyến' : 'Thêm chuyến mới' }}</h5>
                <button type="button" class="btn-close" @click="closeModal"></button>
              </div>
              <div class="modal-body">
                <div class="row g-2">
                  <div class="col-6">
                    <label class="form-label">Loại phương tiện</label>
                    <select class="form-select" v-model="form.type">
                      <option value="bus">Xe khách</option>
                      <option value="train">Tàu</option>
                      <option value="plane">Máy bay</option>
                    </select>
                  </div>
                  <div class="col-6">
                    <label class="form-label">Hãng/nhà xe</label>
                    <input class="form-control" v-model="form.carrier" placeholder="VD: ABC Express" />
                  </div>
                  <div class="col-6">
                    <label class="form-label">Nơi đi</label>
                    <input class="form-control" v-model="form.departureLocation" placeholder="VD: Đà Nẵng" />
                  </div>
                  <div class="col-6">
                    <label class="form-label">Nơi đến</label>
                    <input class="form-control" v-model="form.arrivalLocation" placeholder="VD: Hà Nội" />
                  </div>
                  <div class="col-6">
                    <label class="form-label">Khởi hành</label>
                    <input type="datetime-local" class="form-control" v-model="form.departAt" />
                  </div>
                  <div class="col-6">
                    <label class="form-label">Giá cơ bản (VND)</label>
                    <input type="number" class="form-control" v-model.number="form.basePrice" min="0" />
                  </div>
                  <div class="col-6">
                    <label class="form-label">Tổng ghế</label>
                    <input type="number" class="form-control" v-model.number="form.totalSeats" min="0" />
                  </div>
                  <div class="col-6">
                    <label class="form-label">Còn ghế</label>
                    <input type="number" class="form-control" v-model.number="form.availableSeats" min="0" />
                  </div>
                  <div class="col-6">
                    <label class="form-label">Trạng thái</label>
                    <select class="form-select" v-model="form.status">
                      <option value="AVAILABLE">Còn vé</option>
                      <option value="SOLD_OUT">Hết vé</option>
                      <option value="CANCELLED">Hủy chuyến</option>
                    </select>
                  </div>
                </div>
                <div class="alert alert-info mt-3 mb-0">Đây là form UI mẫu. Chưa lưu dữ liệu thật.</div>
              </div>
              <div class="modal-footer">
                <button class="btn btn-secondary" type="button" @click="closeModal">Đóng</button>
                <button class="btn btn-success" type="submit">Lưu (demo)</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from "vue";

const loading = ref(false);
const showModal = ref(false);
const isEdit = ref(false);

const form = reactive({
  id: undefined,
  type: "bus",
  carrier: "",
  departureLocation: "",
  arrivalLocation: "",
  departAt: "",
  basePrice: 0,
  totalSeats: undefined,
  availableSeats: undefined,
  status: "AVAILABLE",
});

function toggleLoading() {
  loading.value = !loading.value;
}
function openCreate() {
  isEdit.value = false;
  Object.assign(form, {
    id: undefined,
    type: "bus",
    carrier: "",
    departureLocation: "",
    arrivalLocation: "",
    departAt: "",
    basePrice: 0,
    totalSeats: undefined,
    availableSeats: undefined,
    status: "AVAILABLE",
  });
  showModal.value = true;
}
function closeModal() {
  showModal.value = false;
}
</script>

<style scoped>
.page-container {
  padding-top: 20px;
}

/* Header */
.header-bar {
  border-bottom: 2px solid #eef2f6;
  padding-bottom: 10px;
}
.page-title {
  margin: 0;
  color: #000; /* 👈 đổi sang màu đen */
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 8px;
}

/* Nút */
.btn-primary,
.btn-success {
  background-color: #0b3b6e !important;
  border-color: #0b3b6e !important;
}
.btn-outline-primary {
  color: #0b3b6e !important;
  border-color: #0b3b6e !important;
}
.btn-outline-primary:hover {
  background-color: #0b3b6e !important;
  color: #fff !important;
}

/* Skeleton shimmer */
.skeleton {
  display: inline-block;
  height: 12px;
  border-radius: 6px;
  background: linear-gradient(90deg, #e9ecef 25%, #f8f9fa 37%, #e9ecef 63%);
  background-size: 400% 100%;
  animation: shimmer 1.2s ease-in-out infinite;
}
.w-40 { width: 40px; }
.w-50 { width: 50px; }
.w-60 { width: 60px; }
.w-70 { width: 70px; }
.w-80 { width: 80px; }
.w-100 { width: 100%; }

@keyframes shimmer {
  0% { background-position: 100% 0; }
  100% { background-position: 0 0; }
}
/* Canh chỉnh tiêu đề giống Bảng điều khiển */
.page-title {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 1.5rem;       /* cao hơn để ngang hàng */
  font-weight: 700;
  color: #000;
  margin: 0;
  margin-top: 2px;          /* tinh chỉnh cao/thấp */
  padding-left: 4px;        /* đẩy nhẹ sang phải cho đều với icon */
}

/* Giảm khoảng cách phần khung để dính sát topbar hơn */
.page-container,
.quan-ly-don-hang {
  padding-top: 6px;
}

</style>
