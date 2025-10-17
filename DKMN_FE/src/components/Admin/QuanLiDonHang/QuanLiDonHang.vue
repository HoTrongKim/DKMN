<template>
  <div class="quan-ly-don-hang">
    <div class="card p-4 shadow-sm">
      <!-- Header -->
      <div class="header-bar d-flex align-items-center justify-content-between mb-3">
        <h4 class="page-title text-dk-blue m-0">
          <i class="fas fa-file-invoice me-2"></i> Quản Lý Đơn Hàng
        </h4>
        <button class="btn btn-outline-primary" @click="fetchOrders">
          <i class="fas fa-sync-alt me-1"></i> Tải lại dữ liệu
        </button>
      </div>

      <!-- Bộ lọc -->
      <div class="row g-2 mb-3">
        <div class="col-12 col-md-6">
          <div class="input-group searchbox">
            <span class="input-group-text"><i class="fas fa-search"></i></span>
            <input
              type="text"
              class="form-control"
              placeholder="Tìm theo Mã đơn, Khách hàng, SĐT, Tuyến..."
              v-model="searchTerm"
              @input="searchOrders"
            />
          </div>
        </div>
        <div class="col-12 col-md-3">
          <select class="form-select" v-model="filterStatus" @change="searchOrders">
            <option value="">— Lọc theo trạng thái —</option>
            <option value="Đã đặt">Đã đặt (Chờ đi)</option>
            <option value="Đã đi">Đã đi (Hoàn thành)</option>
            <option value="Đã hủy">Đã hủy</option>
            <option value="Đang xử lý">Đang xử lý</option>
          </select>
        </div>
      </div>

      <!-- Bảng -->
      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead class="table-light border-bottom">
            <tr>
              <th>Mã đơn</th>
              <th>Khách hàng</th>
              <th>Chuyến đi</th>
              <th>Ngày đặt</th>
              <th>Tổng tiền</th>
              <th>Trạng thái</th>
              <th class="text-end">Thao tác</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="order in filteredOrders" :key="order.id">
              <td class="fw-semibold text-primary-700">#{{ order.id }}</td>
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
              <td><span :class="getStatusClass(order.status)">{{ order.status }}</span></td>
              <td class="text-end">
                <button class="btn btn-sm btn-outline-info me-2" @click="viewOrder(order)">
                  <i class="fas fa-eye"></i>
                </button>
                <button
                  class="btn btn-sm btn-outline-danger"
                  :disabled="order.status === 'Đã hủy' || order.status === 'Đã đi'"
                  @click="confirmCancel(order)"
                >
                  <i class="fas fa-times"></i>
                </button>
              </td>
            </tr>
            <tr v-if="filteredOrders.length === 0">
              <td colspan="7" class="text-center text-muted py-4">
                <i class="far fa-folder-open fa-lg mb-2"></i><br />
                Không tìm thấy đơn hàng nào.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";

const orders = ref([]);
const filteredOrders = ref([]);
const searchTerm = ref("");
const filterStatus = ref("");

const fetchOrders = () => {
  orders.value = [];
  filteredOrders.value = [];
};

const searchOrders = () => {
  let list = [...orders.value];
  if (filterStatus.value) list = list.filter((o) => o.status === filterStatus.value);
  if (searchTerm.value) {
    const q = searchTerm.value.toLowerCase();
    list = list.filter(
      (o) =>
        String(o.id).includes(q) ||
        o.customerName?.toLowerCase().includes(q) ||
        o.customerPhone?.includes(q)
    );
  }
  filteredOrders.value = list;
};

const confirmCancel = (order) => {
  if (confirm(`Xác nhận hủy đơn hàng #${order.id}?`)) console.log("Hủy:", order.id);
};

const formatCurrency = (v) =>
  typeof v === "number" ? v.toLocaleString("vi-VN", { style: "currency", currency: "VND" }) : "N/A";

const getStatusClass = (s) => {
  switch (s) {
    case "Đã đặt":
      return "badge dk-badge dk-badge-primary";
    case "Đã đi":
      return "badge dk-badge dk-badge-success";
    case "Đã hủy":
      return "badge dk-badge dk-badge-danger";
    case "Đang xử lý":
      return "badge dk-badge dk-badge-warning";
    default:
      return "badge dk-badge dk-badge-secondary";
  }
};

onMounted(fetchOrders);
</script>

<style scoped>
:root {
  --dk-blue: #0b3b6e;
  --dk-light: #eef2f6;
}

/* Header */
.header-bar {
  border-bottom: 2px solid var(--dk-light);
  padding-bottom: 10px;
}
.page-title {
  font-weight: 700;
  color: var(--dk-blue);
}
.text-dk-blue {
  color: var(--dk-blue);
}

/* Search box */
.searchbox .input-group-text {
  background: #fff;
  border-right: none;
  color: #6b7280;
}
.searchbox .form-control {
  border-left: none;
  box-shadow: 0 1px 3px rgba(11, 59, 110, 0.06);
  border-color: #dce3ee;
}
.searchbox .form-control:focus {
  border-color: var(--dk-blue);
  box-shadow: 0 0 0 3px rgba(11, 59, 110, 0.15);
}

/* Nút */
.btn-outline-primary {
  color: var(--dk-blue);
  border-color: var(--dk-blue);
}
.btn-outline-primary:hover {
  background-color: var(--dk-blue);
  color: #fff;
}

/* Badge trạng thái */
.dk-badge {
  padding: 0.45em 0.7em;
  border-radius: 10rem;
  min-width: 90px;
  text-align: center;
  font-weight: 600;
}
.dk-badge-primary {
  background: #e6eff8;
  color: var(--dk-blue);
}
.dk-badge-success {
  background: #e7f6ee;
  color: #17864d;
}
.dk-badge-danger {
  background: #fde8e8;
  color: #c24124;
}
.dk-badge-warning {
  background: #fff3cd;
  color: #7a5c00;
}
.dk-badge-secondary {
  background: #eef2f6;
  color: #475569;
}
.text-primary-700 {
  color: var(--dk-blue);
}
</style>
