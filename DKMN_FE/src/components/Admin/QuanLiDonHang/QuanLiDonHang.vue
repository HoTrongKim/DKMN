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
              :disabled="!selectedId"
              @click="openCancelSelected"
            >
              <i class="fas fa-times me-1"></i> Hủy đơn
            </button>

            <!-- 🔁 Nút tải lại -->
            <button class="btn btn-gradient" @click="fetchOrders">
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
                <td class="fw-semibold text-primary">#{{ order.id }}</td>
                <td>
                  {{ order.customerName }}
                  <small class="text-muted d-block">{{ order.customerPhone }}</small>
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
                  Không có đơn hàng nào hiển thị.
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
          </div>
          <div class="modal-footer bg-light">
            <button type="button" class="btn btn-outline-secondary" @click="closeForm">Đóng</button>
            <button type="button" class="btn btn-primary" @click="closeForm">Lưu (UI)</button>
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
          </div>
          <div class="modal-footer bg-light">
            <button class="btn btn-outline-secondary" @click="showCancel=false">Đóng</button>
            <button class="btn btn-outline-danger" @click="showCancel=false">Hủy (UI)</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
const orders = ref([]), filteredOrders = ref([]), selectedId = ref(null);
const selectedItem = computed(() => orders.value.find(o => o.id === selectedId.value));
const searchTerm = ref(""), filterStatus = ref(""), filterPaymentStatus = ref("");
const fetchOrders = () => { filteredOrders.value = [...orders.value]; selectedId.value = null; };
const searchOrders = () => {
  let list = [...orders.value];
  if (filterStatus.value) list = list.filter(o => o.status === filterStatus.value);
  if (filterPaymentStatus.value) list = list.filter(o => o.paymentStatus === filterPaymentStatus.value);
  if (searchTerm.value) list = list.filter(o => String(o.id).includes(searchTerm.value));
  filteredOrders.value = list;
};
const selectRow = id => selectedId.value = id;
const formatCurrency = v => typeof v === "number" ? v.toLocaleString("vi-VN",{style:"currency",currency:"VND"}) : v;
const getStatusClass = s => ({
  "Đã đặt":"badge dk-badge dk-badge-primary","Đã đi":"badge dk-badge dk-badge-success",
  "Đang xử lý":"badge dk-badge dk-badge-warning","Đã hủy":"badge dk-badge dk-badge-danger"
}[s]||"badge dk-badge dk-badge-secondary");
const getPaymentStatusClass = s => ({
  "Đã TT":"badge dk-badge dk-badge-info","Chờ TT":"badge dk-badge dk-badge-warning","Hoàn tiền":"badge dk-badge dk-badge-secondary"
}[s]||"badge dk-badge dk-badge-secondary");
const showForm = ref(false), showCancel = ref(false);
const form = ref({});
const openEditSelected = ()=>{if(!selectedItem.value)return;Object.assign(form.value,selectedItem.value);showForm.value=true;};
const openCancelSelected = ()=>{if(!selectedItem.value)return;showCancel.value=true;};
const closeForm = ()=>showForm.value=false;
onMounted(fetchOrders);
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
