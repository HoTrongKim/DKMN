<template>
  <div class="bao-cao-thong-ke card p-4 shadow-sm">
    <!-- Tiêu đề -->
    <div class="header-bar d-flex align-items-center justify-content-between mb-3">
      <h4 class="page-title m-0">
        <i class="fas fa-chart-line me-2"></i> Báo Cáo & Thống Kê
      </h4>
      <button class="btn btn-primary" @click="fetchStatistics" :disabled="loading">
        <i class="fas fa-sync-alt me-1"></i> Tải lại
      </button>
    </div>

    <!-- Bộ lọc -->
    <div class="row mb-4 align-items-center">
      <div class="col-md-4 col-lg-3">
        <select class="form-select" v-model="filterPeriod" @change="fetchStatistics" :disabled="loading">
          <option value="week">7 Ngày gần nhất</option>
          <option value="month">Tháng này</option>
          <option value="quarter">Quý này</option>
          <option value="year">Năm này</option>
        </select>
      </div>
      <div class="col-md-8 col-lg-9 text-md-end mt-3 mt-md-0">
        <small class="text-muted" v-if="lastRange">Khoảng: {{ formatRange(lastRange) }}</small>
      </div>
    </div>

    <div v-if="errorMessage" class="alert alert-danger">{{ errorMessage }}</div>

    <!-- Thống kê tổng quan -->
    <div class="row g-4 mb-5">
      <div class="col-lg-3 col-md-6">
        <div class="widget border-start border-4 border-primary p-3 rounded bg-white shadow-sm">
          <h6 class="text-muted">Tổng Doanh Thu</h6>
          <p class="h4 fw-bold text-primary">{{ formatCurrency(statistics.totalRevenue) }}</p>
          <small><i class="fas fa-dollar-sign"></i> Doanh thu gộp</small>
        </div>
      </div>
      <div class="col-lg-3 col-md-6">
        <div class="widget border-start border-4 border-success p-3 rounded bg-white shadow-sm">
          <h6 class="text-muted">Số Vé Bán Ra</h6>
          <p class="h4 fw-bold text-success">{{ formatNumber(statistics.totalTickets) }}</p>
          <small><i class="fas fa-ticket-alt"></i> Vé đã đặt</small>
        </div>
      </div>
      <div class="col-lg-3 col-md-6">
        <div class="widget border-start border-4 border-warning p-3 rounded bg-white shadow-sm">
          <h6 class="text-muted">Tỉ lệ Hủy Vé</h6>
          <p class="h4 fw-bold text-warning">{{ statistics.cancellationRate }}%</p>
          <small><i class="fas fa-times-circle"></i> So với tổng vé</small>
        </div>
      </div>
      <div class="col-lg-3 col-md-6">
        <div class="widget border-start border-4 border-info p-3 rounded bg-white shadow-sm">
          <h6 class="text-muted">Đánh Giá TB</h6>
          <p class="h4 fw-bold text-info">
            <i class="fas fa-star"></i> {{ Number(statistics.averageRating || 0).toFixed(1) }}
          </p>
          <small>Từ {{ statistics.totalReviews }} phản hồi</small>
        </div>
      </div>
    </div>

    <!-- Biểu đồ và danh sách -->
    <div class="row">
      <div class="col-lg-8">
        <div class="card shadow-sm border-0 mb-4">
          <div class="card-header bg-light fw-semibold">Biểu đồ trực quan</div>
          <div class="card-body text-center py-5 text-muted">
            [Biểu đồ doanh thu, lượng khách, phản hồi theo {{ filterPeriod }}]
          </div>
        </div>
      </div>

      <div class="col-lg-4">
        <!-- Tuyến phổ biến nhất -->
        <div class="card shadow-sm border-0 mb-4">
          <div class="card-header bg-light fw-semibold">Tuyến phổ biến nhất</div>
          <div class="card-body">
            <ul class="list-group list-group-flush">
              <li
                v-for="(route, index) in statistics.topRoutes"
                :key="index"
                class="list-group-item d-flex justify-content-between align-items-center"
              >
                {{ index + 1 }}. {{ route.name }}
                <span class="badge bg-primary rounded-pill">{{ route.tickets }} vé</span>
              </li>
              <li v-if="statistics.topRoutes.length === 0" class="list-group-item text-muted">
                Không có dữ liệu tuyến.
              </li>
            </ul>
          </div>
        </div>

        <!-- Nhà xe / Hãng được đánh giá cao nhất -->
        <div class="card shadow-sm border-0">
          <div class="card-header bg-light fw-semibold">Nhà xe / Hãng được đánh giá cao nhất</div>
          <div class="card-body">
            <ul class="list-group list-group-flush">
              <li
                v-for="(company, index) in statistics.topCompanies"
                :key="index"
                class="list-group-item d-flex justify-content-between align-items-center"
              >
                {{ index + 1 }}. {{ company.name }}
                <span class="badge bg-success rounded-pill">
                  <i class="fas fa-star"></i> {{ company.rating }}
                </span>
              </li>
              <li v-if="statistics.topCompanies.length === 0" class="list-group-item text-muted">
                Không có dữ liệu hãng.
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <!-- Ghi chú nghiệp vụ -->
    <div class="alert alert-info mt-4 mb-0">
      <ul class="mb-0">
        <li>Thống kê theo thời gian: lượng vé, doanh thu, tuyến phổ biến nhất, nhà xe được đánh giá cao nhất.</li>
        <li>Biểu đồ trực quan: doanh thu, lượng khách, phản hồi.</li>
      </ul>
    </div>

    <div v-if="loading" class="loading-overlay">
      <div class="spinner-border text-primary" role="status"></div>
      <span class="ms-2">Đang tải dữ liệu...</span>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import api from "../../../services/api";

const filterPeriod = ref("month");
const statistics = ref({
  totalRevenue: 0,
  totalTickets: 0,
  cancellationRate: 0,
  averageRating: 0.0,
  totalReviews: 0,
  topRoutes: [],
  topCompanies: [],
});
const loading = ref(false);
const errorMessage = ref("");
const lastRange = ref(null);

const fetchStatistics = async () => {
  loading.value = true;
  errorMessage.value = "";
  try {
    const { data } = await api.get("/admin/statistics/report", {
      params: { period: filterPeriod.value },
    });
    const payload = data?.data || {};
    statistics.value = {
      totalRevenue: payload.totalRevenue || 0,
      totalTickets: payload.totalTickets || 0,
      cancellationRate: payload.cancellationRate || 0,
      averageRating: payload.averageRating || 0,
      totalReviews: payload.totalReviews || 0,
      topRoutes: payload.topRoutes || [],
      topCompanies: payload.topCompanies || [],
    };
    lastRange.value = payload.range || null;
  } catch (error) {
    errorMessage.value =
      error?.response?.data?.message ||
      "Kh\u00f4ng th\u1ec3 t\u1ea3i d\u1eef li\u1ec7u th\u1ed1ng k\u00ea.";
  } finally {
    loading.value = false;
  }
};

const formatCurrency = (amount) =>
  typeof amount === "number"
    ? amount.toLocaleString("vi-VN", { style: "currency", currency: "VND" })
    : "N/A";

const formatNumber = (value) =>
  typeof value === "number" ? value.toLocaleString("vi-VN") : "0";

const formatRange = (range) => {
  if (!range?.from || !range?.to) return "";
  const from = new Date(range.from);
  const to = new Date(range.to);
  return `${from.toLocaleDateString("vi-VN")} - ${to.toLocaleDateString("vi-VN")}`;
};

onMounted(() => fetchStatistics());
</script>

<style scoped>
:root {
  --dk-blue: #0b3b6e;
  --dk-blue-600: #092f59;
}

.bao-cao-thong-ke {
  position: relative;
}

.page-title {
  color: var(--dk-blue);
  font-weight: 700;
}
.header-bar {
  border-bottom: 2px solid #eef2f6;
  padding-bottom: 10px;
}

.btn-primary {
  background-color: var(--dk-blue);
  border-color: var(--dk-blue);
}
.btn-primary:hover {
  background-color: var(--dk-blue-600);
  border-color: var(--dk-blue-600);
}

.widget {
  border-radius: 10px;
  background: #fff;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.widget:hover {
  transform: translateY(-3px);
  box-shadow: 0 4px 10px rgba(11, 59, 110, 0.1);
}

.card-header {
  color: var(--dk-blue);
}

.loading-overlay {
  position: absolute;
  inset: 0;
  background: rgba(255, 255, 255, 0.72);
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 10px;
  border-radius: 16px;
}
</style>
