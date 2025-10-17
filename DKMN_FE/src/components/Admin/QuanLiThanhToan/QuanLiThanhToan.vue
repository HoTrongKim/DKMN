<template>
  <div class="quan-ly-thanh-toan">
    <div class="card p-4 shadow-sm">
      <!-- HEADER -->
      <div class="header-bar d-flex align-items-center justify-content-between mb-3">
        <h4 class="page-title m-0">
          <i class="fas fa-credit-card me-2"></i> Quản Lý Thanh Toán & Giao Dịch
        </h4>
        <div class="d-flex gap-2">
          <button class="btn btn-outline-primary" @click="fetchTransactions">
            <i class="fas fa-sync-alt me-1"></i> Tải lại
          </button>
          <button class="btn btn-primary" @click="exportReport">
            <i class="fas fa-file-excel me-1"></i> Xuất báo cáo
          </button>
        </div>
      </div>

      <!-- BỘ LỌC -->
      <div class="row g-2 mb-3">
        <div class="col-12 col-md-3">
          <select class="form-select" v-model="filterGateway" @change="fetchTransactions">
            <option value="">— Cổng thanh toán —</option>
            <option value="Momo">Momo</option>
            <option value="ZaloPay">ZaloPay</option>
            <option value="PayPal">PayPal</option>
          </select>
        </div>
        <div class="col-12 col-md-3">
          <select class="form-select" v-model="filterType" @change="fetchTransactions">
            <option value="">— Loại giao dịch —</option>
            <option value="Thanh toán">Thanh toán</option>
            <option value="Hoàn tiền">Hoàn tiền</option>
          </select>
        </div>
        <div class="col-6 col-md-2">
          <input type="date" class="form-control" v-model="dateFrom" @change="fetchTransactions" />
        </div>
        <div class="col-6 col-md-2">
          <input type="date" class="form-control" v-model="dateTo" @change="fetchTransactions" />
        </div>
      </div>

      <!-- TỔNG QUAN -->
      <div class="row g-3 mb-3">
        <div class="col-6 col-lg-3">
          <div class="kpi-card">
            <div class="kpi-label">Doanh thu hôm nay</div>
            <div class="kpi-value">{{ formatCurrency(kpi.todayRevenue) }}</div>
          </div>
        </div>
        <div class="col-6 col-lg-3">
          <div class="kpi-card">
            <div class="kpi-label">Doanh thu tháng này</div>
            <div class="kpi-value">{{ formatCurrency(kpi.monthRevenue) }}</div>
          </div>
        </div>
        <div class="col-6 col-lg-3">
          <div class="kpi-card">
            <div class="kpi-label">Doanh thu quý này</div>
            <div class="kpi-value">{{ formatCurrency(kpi.quarterRevenue) }}</div>
          </div>
        </div>
        <div class="col-6 col-lg-3">
          <div class="kpi-card">
            <div class="kpi-label">Tỉ lệ hủy vé</div>
            <div class="kpi-value">{{ kpi.cancelRate.toFixed(2) }}%</div>
          </div>
        </div>
      </div>

      <!-- LỊCH SỬ GIAO DỊCH -->
      <div class="section-title mt-2 mb-2">
        <h6 class="m-0"><i class="far fa-clock me-2"></i>Lịch sử giao dịch</h6>
      </div>

      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead class="table-light border-bottom">
            <tr>
              <th>Mã GD</th>
              <th>Mã Đơn hàng</th>
              <th>Loại</th>
              <th>Số tiền</th>
              <th>Cổng TT</th>
              <th>Thời gian</th>
              <th>Trạng thái</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="txn in transactions" :key="txn.id">
              <td class="fw-semibold text-primary-700">{{ txn.id }}</td>
              <td>#{{ txn.orderId }}</td>
              <td>
                <span :class="txn.type === 'Hoàn tiền' ? 'badge dk-badge dk-badge-danger' : 'badge dk-badge dk-badge-success'">
                  {{ txn.type }}
                </span>
              </td>
              <td :class="txn.type === 'Hoàn tiền' ? 'text-danger' : 'text-success'">
                {{ formatCurrency(txn.amount) }}
              </td>
              <td>{{ txn.gateway }}</td>
              <td>{{ txn.time }}</td>
              <td><span :class="getTxnStatusClass(txn.status)">{{ txn.status }}</span></td>
            </tr>

            <!-- Empty state -->
            <tr v-if="transactions.length === 0">
              <td colspan="7" class="text-center text-muted py-4">
                <i class="far fa-folder-open fa-lg mb-2"></i><br />
                Không có giao dịch nào được tìm thấy.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- GHI CHÚ NGHIỆP VỤ -->
      <div class="alert alert-info mt-3 mb-0">
        <ul class="mb-0">
          <li>Kết nối và giám sát cổng thanh toán: Momo, ZaloPay, PayPal.</li>
          <li>Xem lịch sử giao dịch; tổng doanh thu theo ngày / tháng / quý.</li>
          <li>Xuất báo cáo tài chính; thống kê doanh thu, tỉ lệ hủy vé.</li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue";

/* State thuần FE (không demo data) */
const transactions = ref([]);
const filterGateway = ref("");
const filterType = ref("");
const dateFrom = ref("");
const dateTo = ref("");

/* KPI placeholders (để FE hiển thị, backend set sau) */
const kpi = ref({
  todayRevenue: 0,
  monthRevenue: 0,
  quarterRevenue: 0,
  cancelRate: 0,
});

/* Khung hàm – sẽ gắn API thật sau */
const fetchTransactions = async () => {
  // TODO: gọi API với filterGateway.value, filterType.value, dateFrom.value, dateTo.value
  // transactions.value = await api.getTransactions({ ...filters })
  // kpi.value = await api.getKpi({ ...filters })
  transactions.value = [];
  kpi.value = { todayRevenue: 0, monthRevenue: 0, quarterRevenue: 0, cancelRate: 0 };
};

const exportReport = async () => {
  // TODO: gọi API xuất báo cáo tài chính
  // await api.exportReport({ ...filters })
  alert("Đang tạo và tải file báo cáo... (FE placeholder)");
};

/* Helpers */
const formatCurrency = (v) =>
  typeof v === "number" ? v.toLocaleString("vi-VN", { style: "currency", currency: "VND" }) : "N/A";

const getTxnStatusClass = (status) => {
  switch (status) {
    case "Thành công":
      return "badge dk-badge dk-badge-primary";
    case "Đang chờ":
      return "badge dk-badge dk-badge-warning";
    case "Thất bại":
      return "badge dk-badge dk-badge-danger";
    case "Đã hoàn tiền":
      return "badge dk-badge dk-badge-secondary";
    default:
      return "badge dk-badge dk-badge-secondary";
  }
};
</script>

<style scoped>
:root {
  --dk-blue: #0b3b6e;
  --dk-blue-600: #0a3563;
  --dk-light: #eef2f6;
}

/* Header */
.header-bar {
  border-bottom: 2px solid var(--dk-light);
  padding-bottom: 10px;
}
.page-title {
  margin: 0;
  color: var(--dk-blue);
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 8px;
}

/* Buttons */
.btn-outline-primary {
  color: var(--dk-blue);
  border-color: var(--dk-blue);
}
.btn-outline-primary:hover {
  background: var(--dk-blue);
  color: #fff;
}
.btn-primary {
  background: var(--dk-blue);
  border-color: var(--dk-blue);
}
.btn-primary:hover {
  background: var(--dk-blue-600);
  border-color: var(--dk-blue-600);
}

/* KPIs */
.kpi-card {
  background: #fff;
  border: 1px solid #e7ecf4;
  border-radius: 12px;
  padding: 14px 16px;
  box-shadow: 0 1px 3px rgba(11, 59, 110, 0.06);
}
.kpi-label {
  color: #667085;
  font-size: 0.9rem;
}
.kpi-value {
  font-size: 1.1rem;
  font-weight: 700;
  color: var(--dk-blue);
  margin-top: 6px;
}

/* Table */
.table thead th {
  color: var(--dk-blue);
  font-weight: 600;
  white-space: nowrap;
}
.text-primary-700 {
  color: var(--dk-blue-600);
}

/* Badge tone (pill) */
.dk-badge {
  padding: 0.45em 0.7em;
  min-width: 90px;
  border-radius: 10rem;
  font-weight: 600;
  text-align: center;
}
.dk-badge-primary { background: #e6eff8; color: var(--dk-blue); }
.dk-badge-success { background: #e7f6ee; color: #17864d; }
.dk-badge-danger  { background: #fde8e8; color: #c24124; }
.dk-badge-warning { background: #fff3cd; color: #7a5c00; }
.dk-badge-secondary { background: #eef2f6; color: #475569; }
</style>
