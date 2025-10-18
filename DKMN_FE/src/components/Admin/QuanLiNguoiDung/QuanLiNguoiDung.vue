<template>
  <div class="qlnd-page">
    <div class="card p-4 shadow-sm rounded-3">
      <!-- ===== HEADER ===== -->
      <div class="header-bar d-flex align-items-center justify-content-between flex-wrap mb-3">
        <h4 class="page-title m-0">
          <i class="fas fa-users me-2"></i> Quản Lý Người Dùng
        </h4>

        <div class="action-bar d-flex flex-wrap gap-2">
          <button type="button" class="btn btn-grad-add" @click="openAdd">
            <i class="fas fa-user-plus me-1"></i> Thêm người dùng
          </button>
          <button type="button" class="btn btn-grad-edit">
            <i class="fas fa-edit me-1"></i> Chỉnh sửa
          </button>
          <button type="button" class="btn btn-grad-lock">
            <i class="fas fa-user-lock me-1"></i> Khóa
          </button>
          <button type="button" class="btn btn-grad-del">
            <i class="fas fa-trash-alt me-1"></i> Xóa
          </button>
        </div>
      </div>

      <!-- ===== BỘ LỌC ===== -->
      <div class="filter-row">
        <div class="filter-group">
          <div class="input-group">
            <span class="input-group-text"><i class="fas fa-search"></i></span>
            <input
              type="text"
              class="form-control"
              placeholder="Tìm theo tên / email / SĐT"
              v-model="filters.keyword"
            />
          </div>
        </div>

        <div class="filter-group">
          <select class="form-select" v-model="filters.status">
            <option value="">— Tất cả trạng thái —</option>
            <option value="active">Hoạt động</option>
            <option value="locked">Bị khóa</option>
          </select>
        </div>

        <div class="filter-group">
          <select class="form-select" v-model="filters.role">
            <option value="">— Tất cả vai trò —</option>
            <option value="customer">Khách hàng</option>
            <option value="admin">Quản trị</option>
          </select>
        </div>

        <div class="filter-group">
          <button type="button" class="btn btn-refresh" @click="resetFilters">
            <i class="fas fa-rotate me-1"></i> Làm mới
          </button>
        </div>
      </div>

      <!-- ===== BẢNG ===== -->
      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead class="table-light border-bottom">
            <tr>
              <th style="width:56px;">#</th>
              <th>Họ Tên</th>
              <th>Email</th>
              <th>SĐT</th>
              <th>Vai trò</th>
              <th>Trạng thái</th>
              <th class="text-center">Thao tác</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td colspan="7" class="text-center text-muted py-4">
                <div class="mb-2"><i class="far fa-folder-open fa-lg"></i></div>
                Chưa có dữ liệu hiển thị
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- ===== MODAL THÊM NGƯỜI DÙNG (UI-only) ===== -->
      <div v-if="showAdd" class="modal-backdrop fade show"></div>
      <div v-if="showAdd" class="modal d-block" tabindex="-1" @click.self="closeAdd">
        <div class="modal-dialog modal-lg">
          <form class="modal-content" @submit.prevent>
            <div class="modal-header bg-soft-primary">
              <h5 class="modal-title">
                <i class="fas fa-user-plus me-2"></i> Thêm người dùng
              </h5>
              <button type="button" class="btn-close" @click="closeAdd"></button>
            </div>

            <div class="modal-body">
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">Họ tên</label>
                  <input class="form-control" v-model.trim="form.name" placeholder="VD: Nguyễn Văn A" />
                </div>
                <div class="col-md-6">
                  <label class="form-label">Email</label>
                  <input type="email" class="form-control" v-model.trim="form.email" placeholder="email@domain.com" />
                </div>
                <div class="col-md-6">
                  <label class="form-label">Số điện thoại</label>
                  <input class="form-control" v-model.trim="form.phone" placeholder="09xxxxxxxx" />
                </div>
                <div class="col-md-6">
                  <label class="form-label">Mật khẩu (UI)</label>
                  <input type="password" class="form-control" v-model="form.password" placeholder="••••••••" />
                </div>

                <div class="col-md-6">
                  <label class="form-label">Vai trò</label>
                  <select class="form-select" v-model="form.role">
                    <option value="customer">Khách hàng</option>
                    <option value="admin">Quản trị</option>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Trạng thái</label>
                  <select class="form-select" v-model="form.status">
                    <option value="active">Hoạt động</option>
                    <option value="locked">Bị khóa</option>
                  </select>
                </div>
              </div>
            </div>

            <div class="modal-footer">
              <button class="btn btn-ghost" type="button" @click="closeAdd">Đóng</button>
              <button class="btn btn-grad-add" type="button" @click="closeAdd">
                <i class="fas fa-check me-1"></i> Lưu (UI)
              </button>
            </div>
          </form>
        </div>
      </div>
      <!-- /MODAL -->
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'

const filters = reactive({ keyword: '', status: '', role: '' })
function resetFilters () {
  Object.assign(filters, { keyword: '', status: '', role: '' })
}

/* Modal thêm người dùng (UI-only) */
const showAdd = ref(false)
const form = reactive({
  name: '',
  email: '',
  phone: '',
  password: '',
  role: 'customer',
  status: 'active'
})
const openAdd = () => { showAdd.value = true }
const closeAdd = () => { showAdd.value = false }
</script>

<style scoped>
/* ===== Page ===== */
.qlnd-page{ padding:16px; background:#f5f7fb; min-height:100%; }

/* ===== Header ===== */
.header-bar{ border-bottom:2px solid #eef2f6; padding-bottom:10px; }
.page-title{
  display:flex; align-items:center; gap:10px;
  font-size:1.6rem; font-weight:800; color:#0f172a; margin:0;
}

/* ===== Action buttons (không mờ) ===== */
.action-bar .btn{
  border-radius:10px; font-weight:700; letter-spacing:.2px;
  border:0; color:#fff; box-shadow:0 2px 6px rgba(0,0,0,.08);
  transition:all .2s ease; opacity:1;
}
.action-bar .btn:hover{ transform:translateY(-1px); filter:brightness(1.06); }

/* Gradients */
.btn-grad-add { background:linear-gradient(135deg,#2f80ed,#56ccf2); }
.btn-grad-edit{ background:linear-gradient(135deg,#f78b2c,#f5c36a); color:#412a00; }
.btn-grad-lock{ background:linear-gradient(135deg,#8a5cf6,#6b21a8); }
.btn-grad-del { background:linear-gradient(135deg,#ed2e2e,#fc835e); }

/* ===== Filter row ===== */
.filter-row{
  display:flex; gap:12px; align-items:end; margin-bottom:20px; flex-wrap:wrap;
}
.filter-group{ flex:1; min-width:200px; }
.filter-group:last-child{ flex:0 0 auto; min-width:auto; }

.input-group-text{ background:#fff; border-right:none; color:#64748b; }
.form-control{ border-left:none; }
.form-control:focus, .form-select:focus{
  border-color:#93c5fd; box-shadow:0 0 0 .2rem rgba(147,197,253,.35);
}

/* Responsive */
@media (max-width: 768px) {
  .filter-row{ flex-direction:column; align-items:stretch; }
  .filter-group{ min-width:auto; }
}

/* Nút làm mới ngang hàng với input/select */
.btn-refresh{
  background:linear-gradient(135deg,#00b4d8,#0096c7);
  color:#fff; font-weight:600; border:none; border-radius:8px;
  padding:6px 12px; box-shadow:0 2px 4px rgba(0,150,199,.25);
  transition:all .2s ease; height:38px; /* Chiều cao bằng form-control */
  display:flex; align-items:center; justify-content:center;
  font-size:0.9rem; white-space:nowrap;
}
.btn-refresh:hover{ filter:brightness(1.08); transform:translateY(-1px); }

/* ===== Table ===== */
.table th{ font-weight:700; color:#111; }
.table-hover tbody tr:hover{ background:#f1f5f9; }

/* ===== Modal ===== */
.modal-backdrop{ position:fixed; inset:0; background:rgba(0,0,0,.4); z-index:1050; }
.modal.d-block{ position:fixed; inset:0; display:block; z-index:1060; }
.modal-content{ border-radius:12px; border:none; overflow:hidden; }
.bg-soft-primary{ background:#eef4ff; border-bottom:1px solid #dce3f2; }
.btn-ghost{ background:#eef2f7; color:#0f172a; border:1px solid #e5e9f2; border-radius:10px; }
</style>
