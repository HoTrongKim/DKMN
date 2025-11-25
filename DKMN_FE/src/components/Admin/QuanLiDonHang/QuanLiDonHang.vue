<template>
  <div class="quan-ly-don-hang-page">
    <div class="page-container">
      <div class="card p-4 shadow-sm rounded-3">
        <!-- ===== HEADER ===== -->
        <div class="header-bar d-flex align-items-center justify-content-between mb-3">
          <div class="d-flex align-items-center gap-2">
            <i class="fas fa-file-invoice text-primary fs-5"></i>
            <h4 class="page-title m-0 text-dark">Quản Lý Đơn Hàng (Đặt Vé)</h4>
          </div>
          <div class="d-flex flex-wrap gap-2">
            <button
              class="btn btn-complete"
              :disabled="!selectedId || actionLoading"
              @click="markAsCompleted"
            >
              <i class="fas fa-check me-1"></i> Hoàn thành
            </button>
            <!-- 🔵 Nút chỉnh sửa -->
            <button
              class="btn btn-edit"
              :disabled="!selectedId"
              @click="openEditSelected"
            >
              <i class="fas fa-edit me-1"></i> Chỉnh sửa
            </button>

            <!-- 🔴 Nút hủy đơn -->
            <button
              class="btn btn-cancel"
              :disabled="!selectedId || cancelLoading"
              @click="openCancelSelected"
            >
              <i class="fas fa-times me-1"></i> Hủy đơn
            </button>

            <!-- 🗑️ Nút xóa đơn -->
            <button
              class="btn btn-delete"
              :disabled="!selectedId || deleteLoading"
              @click="openDeleteSelected"
            >
              <i class="fas fa-trash me-1"></i> Xóa đơn
            </button>

            <!-- 🔁 Nút tải lại -->
            <button class="btn btn-gradient" :disabled="isLoading" @click="fetchOrders">
              <i class="fas fa-rotate me-1"></i> Tải lại
            </button>
          </div>
        </div>

        <!-- ===== BỘ LỌC ===== -->
        <div class="row g-2 mb-3 filter-panel">
          <div class="col-12 col-md-4">
            <div class="input-group">
              <span class="input-group-text"><i class="fas fa-search"></i></span>
              <input
                v-model="searchTerm"
                @input="searchOrders"
                class="form-control"
                placeholder="Tìm theo mã đơn / khách hàng / chuyến đi"
              />
            </div>
          </div>

          <div class="col-6 col-md-3">
            <select class="form-select" v-model="filterStatus" @change="searchOrders">
              <option value="">Tất cả trạng thái</option>
              <option value="Đã đặt">Đã đặt</option>
              <option value="Đã đi">Đã đi</option>
              <option value="Đang xử lý">Đang xử lý</option>
              <option value="Đã hủy">Đã hủy</option>
            </select>
          </div>

          <div class="col-6 col-md-3">
            <select class="form-select" v-model="filterPaymentStatus" @change="searchOrders">
              <option value="">Tất cả thanh toán</option>
              <option value="Đã TT">Đã thanh toán</option>
              <option value="Chờ TT">Chờ thanh toán</option>
              <option value="Hoàn tiền">Đã hoàn tiền</option>
            </select>
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
            <thead class="table-header">
              <tr>
                <th style="width:42px;"></th>
                <th>Mã đơn</th>
                <th>Khách hàng</th>
                <th>Chuyến đi</th>
                <th>Ngày đặt</th>
                <th>Tổng tiền</th>
                <th>Thanh toán</th>
                <th>Trạng thái</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="order in filteredOrders"
                :key="order.id"
                :class="{'row-selected': order.id===selectedId}"
                @click="selectRow(order.id)"
                style="cursor:pointer"
              >
                <td>
                  <input
                    class="form-check-input"
                    type="radio"
                    name="rowSelect"
                    :value="order.id"
                    v-model="selectedId"
                    @click.stop
                  >
                </td>
                <td class="fw-semibold text-primary">
                  {{ order.code || ("#" + order.id) }}
                </td>
                <td>
                  <div class="fw-semibold text-dark">{{ resolveCustomerName(order) }}</div>
                  <small
                    v-if="resolveCustomerEmail(order)"
                    class="text-muted d-block"
                  >
                    {{ resolveCustomerEmail(order) }}
                  </small>
                  <small
                    v-if="resolveCustomerPhone(order)"
                    class="text-muted d-block"
                  >
                    {{ resolveCustomerPhone(order) }}
                  </small>
                </td>
                <td>
                  {{ order.tripDetail }}
                  <small class="text-info d-block">{{ order.departureTime }}</small>
                </td>
                <td>{{ order.orderDate }}</td>
                <td class="text-danger fw-bold">{{ formatCurrency(order.totalAmount) }}</td>
                <td><span :class="getPaymentStatusClass(order.paymentStatus)">{{ order.paymentStatus }}</span></td>
                <td><span :class="getStatusClass(order.status)">{{ order.status }}</span></td>
              </tr>

              <tr v-if="filteredOrders.length === 0">
                <td colspan="8" class="text-center text-muted py-4">
                  <i class="far fa-folder-open fa-lg mb-2"></i><br />
                  {{ emptyMessage }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ===== MODALS (UI only) ===== -->
    <div v-if="showForm" class="modal-backdrop fade show"></div>
    <div v-if="showForm" class="modal d-block" tabindex="-1" @click.self="closeForm">
      <div class="modal-dialog modal-lg">
        <form class="modal-content" @submit.prevent>
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Chỉnh sửa đơn hàng</h5>
            <button type="button" class="btn-close btn-close-white" @click="closeForm"></button>
          </div>
          <div class="modal-body">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Khách hàng</label>
                <input class="form-control" v-model="form.customerName" placeholder="Họ tên khách hàng" />
              </div>
              <div class="col-md-6">
                <label class="form-label">Số điện thoại</label>
                <input class="form-control" v-model="form.customerPhone" placeholder="09xxxxxxxx" />
              </div>
              <div class="col-md-8">
                <label class="form-label">Chuyến đi</label>
                <input class="form-control" v-model="form.tripDetail" placeholder="VD: HN - TPHCM (Máy bay)" />
              </div>
              <div class="col-md-4">
                <label class="form-label">Ngày đặt</label>
                <input type="date" class="form-control" v-model="form.orderDate" />
              </div>
              <div class="col-md-4">
                <label class="form-label">Tổng tiền</label>
                <input type="number" class="form-control" v-model="form.totalAmount" />
              </div>
              <div class="col-md-4">
                <label class="form-label">Thanh toán</label>
                <select class="form-select" v-model="form.paymentStatus">
                  <option>Đã TT</option><option>Chờ TT</option><option>Hoàn tiền</option>
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label">Trạng thái</label>
                <select class="form-select" v-model="form.status">
                  <option>Đã đặt</option><option>Đã đi</option><option>Đang xử lý</option><option>Đã hủy</option>
                </select>
              </div>
            </div>
            <p v-if="formError" class="text-danger small mb-0">{{ formError }}</p>
          </div>
          <div class="modal-footer bg-light">
            <button type="button" class="btn btn-outline-secondary" @click="closeForm">Đóng</button>
            <button
              type="button"
              class="btn btn-primary"
              :disabled="isSubmitting"
              @click="saveForm"
            >
              <span v-if="isSubmitting" class="spinner-border spinner-border-sm me-2" role="status"></span>
              Lưu thay đổi
            </button>
          </div>
        </form>
      </div>
    </div>

    <div v-if="showCancel" class="modal-backdrop fade show"></div>
    <div v-if="showCancel" class="modal d-block" tabindex="-1" @click.self="showCancel=false">
      <div class="modal-dialog modal-sm">
        <div class="modal-content">
          <div class="modal-header bg-danger text-white">
            <h6 class="modal-title"><i class="fas fa-times-circle me-2"></i>Hủy đơn hàng</h6>
            <button type="button" class="btn-close btn-close-white" @click="showCancel=false"></button>
          </div>
          <div class="modal-body">
            Xác nhận hủy đơn <strong>#{{ selectedItem?.id }}</strong>?
            <p v-if="cancelError" class="text-danger small mt-3 mb-0">{{ cancelError }}</p>
          </div>
          <div class="modal-footer bg-light">
            <button class="btn btn-outline-secondary" :disabled="cancelLoading" @click="showCancel=false">Đóng</button>
            <button class="btn btn-outline-danger" :disabled="cancelLoading" @click="confirmCancel">
              <span v-if="cancelLoading" class="spinner-border spinner-border-sm me-2" role="status"></span>
              Hủy đơn
            </button>
          </div>
        </div>
      </div>
    </div>

    <div v-if="showDelete" class="modal-backdrop fade show"></div>
    <div v-if="showDelete" class="modal d-block" tabindex="-1" @click.self="showDelete=false">
      <div class="modal-dialog modal-sm">
        <div class="modal-content">
          <div class="modal-header bg-dark text-white">
            <h6 class="modal-title"><i class="fas fa-trash-alt me-2"></i>Xóa đơn hàng</h6>
            <button type="button" class="btn-close btn-close-white" @click="showDelete=false"></button>
          </div>
          <div class="modal-body">
            Bạn có chắc muốn xóa vĩnh viễn <strong>#{{ selectedItem?.code || selectedItem?.id }}</strong>?
            <p v-if="deleteError" class="text-danger small mt-3 mb-0">{{ deleteError }}</p>
          </div>
          <div class="modal-footer bg-light">
            <button class="btn btn-outline-secondary" :disabled="deleteLoading" @click="showDelete=false">Đóng</button>
            <button class="btn btn-outline-danger" :disabled="deleteLoading" @click="confirmDelete">
              <span v-if="deleteLoading" class="spinner-border spinner-border-sm me-2" role="status"></span>
              Xóa đơn
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from "vue";
import api from "../../../services/api";

const orders = ref([]);
const filteredOrders = ref([]);
const selectedId = ref(null);
const selectedItem = computed(() => orders.value.find(o => o.id === selectedId.value) || null);

const searchTerm = ref("");
const filterStatus = ref("");
const filterPaymentStatus = ref("");
const emptyMessage = ref("Không có đơn hàng nào hiển thị.");

const isLoading = ref(false);
const actionLoading = ref(false);
const isSubmitting = ref(false);
const cancelLoading = ref(false);
const deleteLoading = ref(false);
const actionError = ref("");
const actionMessage = ref("");
const formError = ref("");
const cancelError = ref("");
const deleteError = ref("");

const pagination = ref({ currentPage: 1, lastPage: 1, total: 0 });

const showForm = ref(false);
const showCancel = ref(false);
const showDelete = ref(false);

const filterStatusMap = {
  "Đã đặt": "da_dat",
  "Đang xử lý": "dang_xu_ly",
  "Đã đi": "da_di",
  "Đã hủy": "da_huy",
};

const updateStatusMap = {
  "Đã đặt": "da_xac_nhan",
  "Đang xử lý": "cho_xu_ly",
  "Đã đi": "hoan_tat",
  "Đã hủy": "da_huy",
};

const paymentStatusMap = {
  "Đã TT": "paid",
  "Chờ TT": "pending",
  "Hoàn tiền": "refunded",
};

const createDefaultForm = () => ({
  id: null,
  code: "",
  customerName: "",
  customerPhone: "",
  tripDetail: "",
  orderDate: "",
  totalAmount: 0,
  paymentStatus: "",
  status: "",
});

const form = reactive(createDefaultForm());

const resetForm = () => {
  Object.assign(form, createDefaultForm());
  formError.value = "";
};

const toDateInput = value => {
  if (!value) return "";
  try {
    const d = new Date(value);
    if (Number.isNaN(d.getTime())) return "";
    const local = new Date(d.getTime() - d.getTimezoneOffset() * 60000);
    return local.toISOString().slice(0, 10);
  } catch (error) {
    return "";
  }
};

const fetchOrders = async (page = 1) => {
  isLoading.value = true;
  emptyMessage.value = "Đang tải dữ liệu...";
  actionError.value = "";
  try {
    const params = {
      page,
      search: searchTerm.value?.trim() || undefined,
      status: filterStatusMap[filterStatus.value] || undefined,
      paymentStatus: paymentStatusMap[filterPaymentStatus.value] || undefined,
    };
    const { data } = await api.get("/admin/orders", { params });
    const list = Array.isArray(data?.data) ? data.data : [];
    orders.value = list;
    filteredOrders.value = list;
    pagination.value = data?.meta || { currentPage: page, lastPage: 1, total: list.length };
    selectedId.value = null;
    emptyMessage.value = list.length ? "" : "Không có đơn hàng nào hiển thị.";
  } catch (error) {
    const message = error?.response?.data?.message || error?.message || "Không thể tải danh sách đơn hàng.";
    actionError.value = message;
    actionMessage.value = "";
    window.$toast?.error?.(message);
    orders.value = [];
    filteredOrders.value = [];
    emptyMessage.value = message;
  } finally {
    isLoading.value = false;
  }
};

let searchDebounce;
const searchOrders = () => {
  clearTimeout(searchDebounce);
  searchDebounce = setTimeout(() => fetchOrders(1), 400);
};

const selectRow = id => {
  selectedId.value = id;
};

const resolveCustomerName = (order = {}) =>
  order.accountName || order.customerName || "Khách lẻ";
const resolveCustomerEmail = (order = {}) =>
  order.accountEmail || order.customerEmail || "";
const resolveCustomerPhone = (order = {}) =>
  order.customerPhone || order.accountPhone || "";

const formatCurrency = v => typeof v === "number" ? v.toLocaleString("vi-VN", { style: "currency", currency: "VND" }) : v;

const getStatusClass = s => ({
  "Đã đặt": "badge dk-badge dk-badge-primary",
  "Đã đi": "badge dk-badge dk-badge-success",
  "Đang xử lý": "badge dk-badge dk-badge-warning",
  "Đã hủy": "badge dk-badge dk-badge-danger"
}[s] || "badge dk-badge dk-badge-secondary");

const getPaymentStatusClass = s => ({
  "Đã TT": "badge dk-badge dk-badge-info",
  "Chờ TT": "badge dk-badge dk-badge-warning",
  "Hoàn tiền": "badge dk-badge dk-badge-secondary"
}[s] || "badge dk-badge dk-badge-secondary");

const populateForm = (order) => {
  Object.assign(form, createDefaultForm(), {
    id: order.id,
    code: order.code,
    customerName: order.customerName || "",
    customerPhone: order.customerPhone || "",
    tripDetail: order.tripDetail || "",
    orderDate: toDateInput(order.orderDateRaw),
    totalAmount: order.totalAmount ?? 0,
    paymentStatus: order.paymentStatus || "",
    status: order.status || "",
  });
  formError.value = "";
};

const openEditSelected = () => {
  if (!selectedItem.value) return;
  populateForm(selectedItem.value);
  showForm.value = true;
};

const closeForm = () => {
  if (isSubmitting.value) return;
  showForm.value = false;
  resetForm();
};

const patchOrder = async (orderId, payload) => {
  try {
    await api.patch(`/admin/orders/${orderId}`, payload);
    await fetchOrders(pagination.value.currentPage || 1);
    return { success: true };
  } catch (error) {
    return {
      success: false,
      message: error?.response?.data?.message || error?.message || "Không thể cập nhật đơn hàng.",
    };
  }
};

const saveForm = async () => {
  if (!form.id) return;
  const payload = {};
  const statusCode = updateStatusMap[form.status];
  if (statusCode) payload.status = statusCode;
  const paymentCode = paymentStatusMap[form.paymentStatus];
  if (paymentCode) payload.paymentStatus = paymentCode;

  if (!Object.keys(payload).length) {
    showForm.value = false;
    resetForm();
    return;
  }

  isSubmitting.value = true;
  formError.value = "";
  const result = await patchOrder(form.id, payload);
  isSubmitting.value = false;
  if (result.success) {
    showForm.value = false;
    resetForm();
    actionMessage.value = "Đã cập nhật đơn hàng.";
    actionError.value = "";
    window.$toast?.success?.("Cập nhật đơn hàng thành công! ✅");
  } else {
    formError.value = result.message;
    window.$toast?.error?.(result.message);
  }
};

const openCancelSelected = () => {
  if (!selectedItem.value) return;
  cancelError.value = "";
  showCancel.value = true;
};

const confirmCancel = async () => {
  if (!selectedItem.value || cancelLoading.value) return;
  cancelLoading.value = true;
  cancelError.value = "";
  const result = await patchOrder(selectedItem.value.id, { status: "da_huy" });
  cancelLoading.value = false;
  if (result.success) {
    showCancel.value = false;
    actionMessage.value = "Đơn hàng đã được hủy.";
    actionError.value = "";
    window.$toast?.success?.("Đơn hàng đã được hủy thành công! ❌");
  } else {
    cancelError.value = result.message;
    actionError.value = result.message;
    window.$toast?.error?.(result.message);
  }
};

const openDeleteSelected = () => {
  if (!selectedItem.value) return;
  deleteError.value = "";
  showDelete.value = true;
};

const confirmDelete = async () => {
  if (!selectedItem.value || deleteLoading.value) return;
  deleteLoading.value = true;
  deleteError.value = "";
  try {
    await api.delete(`/admin/orders/${selectedItem.value.id}`);
    actionMessage.value = "Đã xóa đơn hàng.";
    actionError.value = "";
    showDelete.value = false;
    window.$toast?.success?.("Đã xóa đơn hàng thành công! 🗑️");
    await fetchOrders(pagination.value.currentPage || 1);
  } catch (error) {
    const message = error?.response?.data?.message || error?.message || "Không thể xóa đơn hàng.";
    deleteError.value = message;
    actionError.value = message;
    window.$toast?.error?.(message);
  } finally {
    deleteLoading.value = false;
  }
};

const markAsCompleted = async () => {
  if (!selectedItem.value || actionLoading.value) return;
  actionLoading.value = true;
  const result = await patchOrder(selectedItem.value.id, { status: "hoan_tat" });
  actionLoading.value = false;
  if (result.success) {
    actionMessage.value = "Đơn hàng đã được đánh dấu hoàn thành.";
    actionError.value = "";
    window.$toast?.success?.("Đơn hàng đã hoàn thành! ✅");
  } else {
    actionError.value = result.message;
    window.$toast?.error?.(result.message);
  }
};

onMounted(() => fetchOrders());
</script>

<style scoped>
.quan-ly-don-hang-page { padding:20px; background:linear-gradient(180deg,#f7faff,#eef4ff); min-height:100vh; }
.header-bar { border-bottom:1px solid #e5e7eb; padding-bottom:10px; }
.page-title { font-weight:700; color:#1e3a8a; }

/* 🌈 Nút chỉnh sửa */
.btn-edit {
  background: linear-gradient(135deg,#2563eb,#1d4ed8);
  color:#fff; font-weight:600; border:none; box-shadow:0 3px 8px rgba(37,99,235,0.3);
  transition: all .2s;
}
.btn-edit:hover { filter:brightness(1.1); transform:translateY(-1px); }

/* ❤️ Nút hủy đơn */
.btn-cancel {
  background: linear-gradient(135deg,#dc2626,#991b1b);
  color:#fff; font-weight:600; border:none; box-shadow:0 3px 8px rgba(220,38,38,0.3);
  transition: all .2s;
}
.btn-cancel:hover { filter:brightness(1.1); transform:translateY(-1px); }

.btn-delete {
  background: linear-gradient(135deg,#4b5563,#111827);
  color:#fff; font-weight:600; border:none; box-shadow:0 3px 8px rgba(15,23,42,0.35);
  transition: all .2s;
}
.btn-delete:hover { filter:brightness(1.05); transform:translateY(-1px); }

/* ✅ Nút hoàn thành */
.btn-complete {
  background: linear-gradient(135deg,#16a34a,#22c55e);
  color:#fff; font-weight:600; border:none;
  box-shadow:0 3px 8px rgba(34,197,94,0.3);
  transition: all .2s;
}
.btn-complete:hover {
  filter:brightness(1.1);
  transform:translateY(-1px);
}

/* 🔵 Nút tải lại */
.btn-gradient {
  background: linear-gradient(135deg,#4f46e5,#3b82f6);
  color:#fff; border:none; font-weight:600;
  box-shadow:0 3px 8px rgba(79,70,229,0.3);
}
.btn-gradient:hover { filter:brightness(1.1); transform:translateY(-1px); }

/* Bảng */
.table-header { background:linear-gradient(90deg,#3b82f6,#2563eb); color:#fff; }
.table-hover tbody tr:hover { background:#f0f7ff; }
.row-selected { background:#eaf2ff !important; box-shadow:inset 0 0 0 2px #3b82f6; }

.dk-badge { padding:0.4em 0.7em; border-radius:999px; font-weight:600; font-size:0.85rem; }
.dk-badge-primary { background:#e0ebff; color:#2563eb; }
.dk-badge-success { background:#e6f9ee; color:#198754; }
.dk-badge-danger { background:#fde8e8; color:#dc3545; }
.dk-badge-warning { background:#fff8e1; color:#b7791f; }
.dk-badge-info { background:#e0f2fe; color:#0284c7; }
.dk-badge-secondary { background:#f1f5f9; color:#475569; }

.modal-header.bg-primary { background:linear-gradient(135deg,#2563eb,#1e40af)!important; }
.modal-header.bg-danger { background:linear-gradient(135deg,#dc2626,#991b1b)!important; }
</style>

