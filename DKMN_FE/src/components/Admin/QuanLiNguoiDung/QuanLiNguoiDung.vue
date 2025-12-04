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
          <button
            type="button"
            class="btn btn-grad-edit"
            :disabled="!selectedUser || actionLoading"
            @click="openEditSelected"
          >
            <i class="fas fa-edit me-1"></i> Chỉnh sửa
          </button>
          <button
            type="button"
            class="btn btn-grad-lock"
            :disabled="!selectedUser || actionLoading"
            @click="toggleLockSelected"
          >
            <i class="fas fa-user-lock me-1"></i> {{ lockButtonLabel }}
          </button>
          <button
            type="button"
            class="btn btn-grad-del"
            :disabled="!selectedUser || actionLoading"
            @click="deleteSelected"
          >
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

      <div v-if="actionError" class="alert alert-danger py-2 px-3">
        {{ actionError }}
      </div>
      <div v-else-if="actionMessage" class="alert alert-success py-2 px-3">
        {{ actionMessage }}
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
            <tr
              v-for="(user, index) in users"
              :key="user.id"
              :class="{'row-selected': user.id === selectedId}"
              @click="selectUser(user.id)"
              style="cursor:pointer"
            >
              <td class="fw-semibold">{{ index + 1 }}</td>
              <td>
                {{ user.name || '--' }}
                <small class="text-muted d-block">{{ user.createdAtText || '' }}</small>
              </td>
              <td>{{ user.email }}</td>
              <td>{{ user.phone || '--' }}</td>
              <td>
                <span class="badge bg-primary-subtle text-primary">{{ user.role }}</span>
              </td>
              <td>
                <span :class="statusBadge(user.statusCode)">{{ user.status }}</span>
              </td>
              <td class="text-center">
                <div class="btn-group btn-group-sm">
                  <button type="button" class="btn btn-link text-primary" :disabled="actionLoading" @click.stop="startEdit(user)">
                    Sửa
                  </button>
                  <button
                    type="button"
                    class="btn btn-link"
                    :class="user.statusCode === 'locked' ? 'text-success' : 'text-warning'"
                    :disabled="actionLoading"
                    @click.stop="toggleLock(user)"
                  >
                    {{ user.statusCode === 'locked' ? 'Mở khóa' : 'Khóa' }}
                  </button>
                  <button type="button" class="btn btn-link text-danger" :disabled="actionLoading" @click.stop="deleteUser(user)">
                    Xóa
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="!users.length">
              <td colspan="7" class="text-center text-muted py-4">
                <div class="mb-2"><i class="far fa-folder-open fa-lg"></i></div>
                {{ emptyMessage }}
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
                <i class="fas fa-user-plus me-2"></i>
                {{ isEditMode ? 'Chỉnh sửa người dùng' : 'Thêm người dùng' }}
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
                  <label class="form-label">Mật khẩu</label>
                  <input
                    type="password"
                    class="form-control"
                    v-model.trim="form.password"
                    :placeholder="isEditMode ? 'Để trống nếu giữ nguyên' : '********'"
                  />
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
              <p v-if="formError" class="text-danger small mt-3 mb-0">{{ formError }}</p>
            </div>

            <div class="modal-footer">
              <button class="btn btn-ghost" type="button" @click="closeAdd">Đóng</button>
              <button class="btn btn-grad-add" type="button" :disabled="formSubmitting" @click="submitForm">
                <span v-if="formSubmitting" class="spinner-border spinner-border-sm me-1"></span>
                <i v-else class="fas fa-check me-1"></i> Lưu
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
import { reactive, ref, computed, onMounted, watch } from "vue";
import api from "../../../services/api";

const filters = reactive({ keyword: '', status: '', role: '' });
const users = ref([]);
const emptyMessage = ref('Chưa có dữ liệu hiển thị.');
const isLoading = ref(false);
const actionLoading = ref(false);
const actionMessage = ref('');
const actionError = ref('');
const pagination = ref({ currentPage: 1, lastPage: 1, total: 0 });

const selectedId = ref(null);
const selectedUser = computed(() => users.value.find(user => user.id === selectedId.value) || null);
const lockButtonLabel = computed(() => (selectedUser.value?.statusCode === 'locked' ? 'Mở khóa' : 'Khóa'));

const showAdd = ref(false);
const isEditMode = ref(false);
const formSubmitting = ref(false);
const formError = ref('');
const form = reactive({
  name: '',
  email: '',
  phone: '',
  password: '',
  role: 'customer',
  status: 'active',
});

const defaultForm = () => ({
  name: '',
  email: '',
  phone: '',
  password: '',
  role: 'customer',
  status: 'active',
});

const resetForm = () => {
  Object.assign(form, defaultForm());
  formError.value = '';
};

const selectUser = (id) => {
  selectedId.value = id;
};

/**
 * Tải danh sách người dùng từ server.
 * 
 * API: `GET /admin/users`
 * Backend Controller: `AdminUserController::index` (dự đoán)
 * 
 * Logic:
 * 1. Chuẩn bị tham số lọc (`keyword`, `status`, `role`).
 * 2. Gọi API lấy danh sách người dùng.
 * 3. Cập nhật `users` và `pagination`.
 * 4. Kiểm tra xem user đang chọn có còn trong danh sách không, nếu không thì bỏ chọn.
 * 5. Xử lý lỗi và hiển thị thông báo.
 */
const fetchUsers = async (page = 1) => {
  isLoading.value = true;
  emptyMessage.value = 'Đang tải dữ liệu...';
  try {
    const params = {
      page,
      keyword: filters.keyword?.trim() || undefined,
      status: filters.status || undefined,
      role: filters.role || undefined,
    };
    const { data } = await api.get('/admin/users', { params });
    const list = Array.isArray(data?.data) ? data.data : [];
    users.value = list;
    pagination.value = data?.meta || { currentPage: page, lastPage: 1, total: list.length };
    if (!list.some(user => user.id === selectedId.value)) {
      selectedId.value = null;
    }
    emptyMessage.value = list.length ? '' : 'Không có người dùng phù hợp.';
  } catch (error) {
    const message = error?.response?.data?.message || error?.message || 'Không thể tải danh sách người dùng.';
    actionError.value = message;
    actionMessage.value = '';
    window.$toast?.error?.(message);
    users.value = [];
    emptyMessage.value = message;
  } finally {
    isLoading.value = false;
  }
};

const applyFilters = () => fetchUsers(1);

let keywordDebounce;
watch(
  () => filters.keyword,
  () => {
    clearTimeout(keywordDebounce);
    keywordDebounce = setTimeout(applyFilters, 400);
  }
);

watch(
  () => [filters.status, filters.role],
  () => {
    applyFilters();
  }
);

function resetFilters () {
  Object.assign(filters, { keyword: '', status: '', role: '' });
  fetchUsers(1);
}

const openAdd = () => {
  isEditMode.value = false;
  resetForm();
  showAdd.value = true;
};

const startEdit = (user) => {
  if (!user) return;
  selectedId.value = user.id;
  isEditMode.value = true;
  form.name = user.name || '';
  form.email = user.email || '';
  form.phone = user.phone || '';
  form.role = user.roleCode || 'customer';
  form.status = user.statusCode || 'active';
  form.password = '';
  formError.value = '';
  showAdd.value = true;
};

const openEditSelected = () => {
  if (!selectedUser.value) return;
  startEdit(selectedUser.value);
};

const closeAdd = () => {
  if (formSubmitting.value) return;
  showAdd.value = false;
  isEditMode.value = false;
  resetForm();
};

const buildPayload = () => {
  const payload = {
    name: form.name?.trim(),
    email: form.email?.trim(),
    phone: form.phone?.trim() || null,
    role: form.role,
    status: form.status,
  };
  if (!isEditMode.value || form.password) {
    payload.password = form.password;
  }
  return payload;
};

/**
 * Thêm mới hoặc cập nhật thông tin người dùng.
 * 
 * API: 
 * - Tạo mới: `POST /admin/users`
 * - Cập nhật: `PATCH /admin/users/{id}`
 * Backend Controller: `AdminUserController::store` / `AdminUserController::update` (dự đoán)
 * 
 * Logic:
 * 1. Chuẩn bị payload từ form (`buildPayload`).
 * 2. Nếu là chế độ sửa (`isEditMode`):
 *    - Gọi API cập nhật.
 *    - Hiển thị thông báo cập nhật thành công.
 * 3. Nếu là chế độ thêm mới:
 *    - Gọi API tạo mới.
 *    - Hiển thị thông báo thêm mới thành công.
 * 4. Đóng modal, reset form, tải lại danh sách.
 * 5. Xử lý lỗi nếu có.
 */
const submitForm = async () => {
  formSubmitting.value = true;
  formError.value = '';
  try {
    const payload = buildPayload();
    if (isEditMode.value) {
      if (!selectedUser.value) throw new Error('Không tìm thấy người dùng đã chọn.');
      await api.patch(`/admin/users/${selectedUser.value.id}`, payload);
      actionMessage.value = 'Đã cập nhật người dùng.';
      window.$toast?.success?.('Cập nhật người dùng thành công! ✅');
    } else {
      await api.post('/admin/users', payload);
      actionMessage.value = 'Đã thêm người dùng mới.';
      window.$toast?.success?.('Thêm người dùng mới thành công! 👤');
    }
    actionError.value = '';
    showAdd.value = false;
    resetForm();
    const page = isEditMode.value ? pagination.value.currentPage || 1 : 1;
    await fetchUsers(page);
  } catch (error) {
    const errorMsg = error?.response?.data?.message || error?.message || 'Không thể lưu người dùng.';
    formError.value = errorMsg;
    window.$toast?.error?.(errorMsg);
  } finally {
    formSubmitting.value = false;
  }
};

/**
 * Khóa hoặc mở khóa tài khoản người dùng.
 * 
 * API: `PATCH /admin/users/{id}/status`
 * Backend Controller: `AdminUserController::updateStatus` (dự đoán)
 * 
 * Logic:
 * 1. Xác định trạng thái tiếp theo (locked <-> active).
 * 2. Gọi API cập nhật trạng thái.
 * 3. Hiển thị thông báo thành công.
 * 4. Tải lại danh sách người dùng.
 * 5. Xử lý lỗi nếu có.
 */
const lockOrUnlock = async (user) => {
  if (!user || actionLoading.value) return;
  const nextStatus = user.statusCode === 'locked' ? 'active' : 'locked';
  actionLoading.value = true;
  try {
    await api.patch(`/admin/users/${user.id}/status`, { status: nextStatus });
    const successMsg = nextStatus === 'locked' ? 'Đã khóa người dùng.' : 'Đã mở khóa người dùng.';
    actionMessage.value = successMsg;
    actionError.value = '';
    window.$toast?.success?.(successMsg + ' 🔒');
    await fetchUsers(pagination.value.currentPage || 1);
  } catch (error) {
    const errorMsg = error?.response?.data?.message || error?.message || 'Không thể cập nhật trạng thái.';
    actionError.value = errorMsg;
    actionMessage.value = '';
    window.$toast?.error?.(errorMsg);
  } finally {
    actionLoading.value = false;
  }
};

const toggleLockSelected = () => {
  if (!selectedUser.value) return;
  lockOrUnlock(selectedUser.value);
};

const toggleLock = (user) => {
  if (!user) return;
  selectUser(user.id);
  lockOrUnlock(user);
};

/**
 * Xóa người dùng.
 * 
 * API: `DELETE /admin/users/{id}`
 * Backend Controller: `AdminUserController::destroy` (dự đoán)
 * 
 * Logic:
 * 1. Hiển thị confirm dialog.
 * 2. Gọi API xóa người dùng.
 * 3. Hiển thị thông báo thành công.
 * 4. Nếu user bị xóa đang được chọn, bỏ chọn.
 * 5. Tải lại danh sách người dùng (về trang 1).
 * 6. Xử lý lỗi nếu có.
 */
const deleteUser = async (user) => {
  if (!user || actionLoading.value) return;
  selectUser(user.id);
  if (typeof window !== 'undefined' && !window.confirm('Xác nhận xóa người dùng này?')) {
    return;
  }
  actionLoading.value = true;
  try {
    await api.delete(`/admin/users/${user.id}`);
    actionMessage.value = 'Đã xóa người dùng.';
    actionError.value = '';
    window.$toast?.success?.('Đã xóa người dùng thành công! 🗑️');
    if (selectedId.value === user.id) {
      selectedId.value = null;
    }
    await fetchUsers(1);
  } catch (error) {
    const errorMsg = error?.response?.data?.message || error?.message || 'Không thể xóa người dùng.';
    actionError.value = errorMsg;
    actionMessage.value = '';
    window.$toast?.error?.(errorMsg);
  } finally {
    actionLoading.value = false;
  }
};

const deleteSelected = () => {
  if (!selectedUser.value) return;
  deleteUser(selectedUser.value);
};

const statusBadge = (code) => (
  code === 'locked' ? 'badge bg-danger-subtle text-danger' : 'badge bg-success-subtle text-success'
);

onMounted(() => fetchUsers());
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
.row-selected{ background:#eef4ff !important; }
.table .btn-link{ text-decoration:none; font-weight:600; }
.table .btn-link:disabled{ opacity:0.5; pointer-events:none; }

/* ===== Modal ===== */
.modal-backdrop{ position:fixed; inset:0; background:rgba(0,0,0,.4); z-index:1050; }
.modal.d-block{ position:fixed; inset:0; display:block; z-index:1060; }
.modal-content{ border-radius:12px; border:none; overflow:hidden; }
.bg-soft-primary{ background:#eef4ff; border-bottom:1px solid #dce3f2; }
.btn-ghost{ background:#eef2f7; color:#0f172a; border:1px solid #e5e9f2; border-radius:10px; }
</style>

