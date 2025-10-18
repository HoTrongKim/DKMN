<template>
  <div class="qltt-page">
    <div class="card p-4 shadow-sm rounded-3">

      <!-- ===== PHẦN ĐẦU TRANG ===== -->
      <div class="header-bar d-flex align-items-center justify-content-between flex-wrap mb-3">
        <h4 class="page-title m-0">
          <i class="fas fa-credit-card me-2"></i> Quản Lý Thanh Toán & Giao Dịch
        </h4>

        <div class="actions d-flex flex-wrap gap-2">
          <button type="button" class="btn btn-grad-refresh" @click="fetchTransactions">
            <i class="fas fa-rotate me-1"></i> Tải lại
          </button>
          <button type="button" class="btn btn-grad-export" @click="exportReport">
            <i class="fas fa-file-export me-1"></i> Xuất báo cáo
          </button>
        </div>
      </div>

      <!-- ===== BỘ LỌC ===== -->
  <div class="row g-2 align-items-stretch mb-3 filters-row">
        <div class="col-12 col-lg-3">
          <select class="form-select h-100" v-model="filters.gateway" @change="fetchTransactions">
            <option value="">— Cổng thanh toán —</option>
            <option value="Momo">Momo</option>
            <option value="ZaloPay">ZaloPay</option>
            <option value="PayPal">PayPal</option>
          </select>
        </div>

        <div class="col-12 col-lg-3">
          <select class="form-select h-100" v-model="filters.type" @change="fetchTransactions">
            <option value="">— Loại giao dịch —</option>
            <option value="Thanh toán">Thanh toán</option>
            <option value="Hoàn tiền">Hoàn tiền</option>
          </select>
        </div>

        <div class="col-6 col-lg-2">
          <input type="date" class="form-control h-100" v-model="filters.dateFrom" @change="fetchTransactions" />
        </div>
        <div class="col-6 col-lg-2">
          <input type="date" class="form-control h-100" v-model="filters.dateTo" @change="fetchTransactions" />
        </div>

        <div class="col-12 col-lg-2">
          <div class="input-group h-100">
            <span class="input-group-text"><i class="fas fa-search"></i></span>
            <input
              class="form-control"
              placeholder="Mã GD / Mã ĐH"
              v-model="filters.keyword"
              @keyup.enter="fetchTransactions"
            />
        </div>
        </div>
      </div>

      <!-- ===== THẺ THỐNG KÊ ===== -->
      <div class="kpi-section mb-4">
        <div class="kpi-grid">
          <div class="kpi-card kpi-card-primary">
            <div class="kpi-icon">
              <i class="fas fa-calendar-day"></i>
            </div>
            <div class="kpi-content">
            <div class="kpi-label">Doanh thu hôm nay</div>
              <div class="kpi-value">{{ fmtMoney(kpi.todayRevenue) }}</div>
          </div>
        </div>
          
          <div class="kpi-card kpi-card-success">
            <div class="kpi-icon">
              <i class="fas fa-calendar-alt"></i>
        </div>
            <div class="kpi-content">
            <div class="kpi-label">Doanh thu tháng này</div>
              <div class="kpi-value">{{ fmtMoney(kpi.monthRevenue) }}</div>
          </div>
        </div>
          
          <div class="kpi-card kpi-card-info">
            <div class="kpi-icon">
              <i class="fas fa-chart-line"></i>
        </div>
            <div class="kpi-content">
            <div class="kpi-label">Doanh thu quý này</div>
              <div class="kpi-value">{{ fmtMoney(kpi.quarterRevenue) }}</div>
            </div>
          </div>
          
          <div class="kpi-card kpi-card-warning">
            <div class="kpi-icon">
              <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="kpi-content">
              <div class="kpi-label">Tỉ lệ hủy vé</div>
              <div class="kpi-value">{{ (kpi.cancelRate ?? 0).toFixed(2) }}%</div>
        </div>
          </div>
        </div>
      </div>

      <!-- ===== TRẠNG THÁI CỔNG THANH TOÁN ===== -->
      <div class="gateway-section mb-4">
          <div class="section-header">
          <h6 class="section-title">
            <i class="fas fa-plug me-2"></i>Tình trạng kết nối cổng thanh toán
          </h6>
          <button class="btn btn-sm btn-check" @click="checkGateways">
            <i class="fas fa-sync-alt me-1"></i> Kiểm tra lại
          </button>
        </div>
        <div class="gateway-grid">
          <div class="gateway-card" v-for="gw in gateways" :key="gw.code">
            <div class="gateway-header">
              <div class="gateway-icon-img">
                <img :src="getGatewayImage(gw.code)" :alt="gw.name" class="gateway-img" />
              </div>
              <div class="gateway-status">
                <span :class="['status-indicator', gw.status]"></span>
                <span :class="['status-text', gwStatusBadge(gw.status)]">{{ gwStatusText(gw.status) }}</span>
              </div>
            </div>
            <div class="gateway-body">
              <div class="gateway-name">{{ gw.name }}</div>
              <div class="gateway-metrics">
                <div class="metric">
                  <span class="metric-label">Ping:</span>
                  <span class="metric-value">{{ gw.latency ?? '—' }} ms</span>
                </div>
                <div class="metric">
                  <span class="metric-label">Uptime:</span>
                  <span class="metric-value">{{ gw.uptime ?? '—' }}%</span>
                </div>
          </div>
        </div>
          </div>
        </div>
      </div>

      <!-- ===== DANH SÁCH GIAO DỊCH ===== -->
      <div class="transaction-section">
        <div class="section-header">
          <h6 class="section-title">
            <i class="far fa-clock me-2"></i>Lịch sử giao dịch
          </h6>
          <div class="transaction-stats">
            <div :class="['transaction-total', {zero: total === 0}]">
              <div class="total-number">{{ total }}</div>
              <div class="total-label">giao dịch</div>
            </div>
            <div class="stats-actions">
              <button class="btn btn-sm btn-refresh me-2" @click="refreshTransactions">
                <i class="fas fa-sync-alt me-1"></i> Làm mới
              </button>
              <button class="btn btn-sm btn-check" @click="checkGateways">
                <i class="fas fa-plug me-1"></i> Kiểm tra
              </button>
            </div>
        </div>
      </div>

        <div class="table-container">
      <div class="table-responsive">
        <table class="table table-hover align-middle">
              <thead class="table-header">
            <tr>
                  <th class="text-center">#</th>
              <th>Mã GD</th>
                  <th>Mã ĐH</th>
              <th>Loại</th>
              <th>Số tiền</th>
              <th>Cổng TT</th>
              <th>Thời gian</th>
              <th>Trạng thái</th>
                  <!-- Thao tác column removed per request -->
            </tr>
          </thead>
              <tbody v-if="transactions.length === 0">
                <tr>
                  <td colspan="8" class="text-center text-muted py-5">
                    <div class="empty-state">
                      <i class="far fa-folder-open fa-3x mb-3 text-muted"></i>
                      <h6 class="text-muted">Không có giao dịch nào</h6>
                      <p class="text-muted small">Thử thay đổi bộ lọc để xem thêm kết quả</p>
                    </div>
              </td>
            </tr>
              </tbody>
              <tbody v-else>
                <tr v-for="(txn, i) in transactions" :key="txn.id || i" class="transaction-row">
                  <td class="text-center">{{ i + 1 }}</td>
                  <td>
                    <span class="transaction-id">{{ txn.id }}</span>
                  </td>
                  <td>
                    <span class="order-id">#{{ txn.orderId }}</span>
                  </td>
                  <td>
                    <span :class="getTransactionTypeClass(txn.type)">
                      <i :class="getTransactionTypeIcon(txn.type)"></i>
                  {{ txn.type }}
                </span>
              </td>
                  <td>
                    <span :class="getAmountClass(txn.type)">
                      {{ fmtMoney(txn.amount) }}
                    </span>
                  </td>
                  <td>
                    <span class="gateway-badge" :class="getGatewayClass(txn.gateway)">
                      <i :class="getGatewayIcon(txn.gateway.toLowerCase())"></i>
                      {{ txn.gateway }}
                    </span>
                  </td>
                  <td>
                    <div class="datetime">
                      <div class="date">{{ formatDate(txn.time) }}</div>
                      <div class="time">{{ formatTime(txn.time) }}</div>
                    </div>
                  </td>
                  <td>
                    <span :class="txnStatusBadge(txn.status)">
                      <i :class="getStatusIcon(txn.status)"></i>
                      {{ txn.status }}
                    </span>
              </td>
                  <!-- Thao tác column removed -->
            </tr>
          </tbody>
        </table>
          </div>
        </div>
      </div>

      <!-- ===== PHÂN TRANG ===== -->
      <div class="d-flex justify-content-between align-items-center">
        <small class="text-muted">Trang {{ page }}/{{ totalPages || 1 }}</small>
        <ul class="pagination mb-0">
          <li :class="['page-item', {disabled: page===1}]">
            <button class="page-link" @click="goPrev">«</button>
            </li>
          <li class="page-item disabled"><span class="page-link">{{ page }}</span></li>
          <li :class="['page-item', {disabled: page===totalPages || totalPages===0}]">
            <button class="page-link" @click="goNext">»</button>
            </li>
          </ul>
      </div>

      <!-- ===== GHI CHÚ HƯỚNG DẪN ===== -->
      <div class="alert alert-info mt-3 mb-0">
        <ul class="mb-0">
          <li>Kết nối & giám sát cổng thanh toán: Momo, ZaloPay, PayPal.</li>
          <li>Xem lịch sử giao dịch; tổng doanh thu theo ngày / tháng / quý.</li>
          <li>Xuất báo cáo tài chính; thống kê doanh thu và tỉ lệ hủy vé.</li>
        </ul>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'

/* ===== BỘ LỌC VÀ PHÂN TRANG ===== */
const filters = reactive({
  gateway: '',
  type: '',
  dateFrom: '',
  dateTo: '',
  keyword: '', // mã giao dịch / mã đơn hàng
})

/* ===== DỮ LIỆU THỐNG KÊ (sẽ kết nối backend) ===== */
const kpi = ref({
  todayRevenue: 0,
  monthRevenue: 0,
  quarterRevenue: 0,
  cancelRate: 0,
})

/* Trạng thái cổng thanh toán, backend sẽ trả về status & latency */
const gateways = ref([
  { code: 'momo',    name: 'Momo',    status: 'unknown', latency: null },
  { code: 'zalopay', name: 'ZaloPay', status: 'unknown', latency: null },
  { code: 'paypal',  name: 'PayPal',  status: 'unknown', latency: null },
])

/* Danh sách giao dịch (để trống, sẽ kết nối API sau) */
const transactions = ref([])
const total = ref(0)
const page = ref(1)
const pageSize = ref(10)
const totalPages = computed(() => Math.ceil((total.value || 0) / pageSize.value))

/* ===== CÁC HÀM API ===== */
async function fetchTransactions () {
  // TODO: gọi API với bộ lọc + trang + kích thước trang
  // const { data, total: t, kpi: kpiData, gateways: gw } = await api.getTransactions({ ...filters, page: page.value, pageSize: pageSize.value })
  // transactions.value = data; total.value = t; kpi.value = kpiData; gateways.value = gw
  transactions.value = []
  total.value = 0
  kpi.value = { todayRevenue: 0, monthRevenue: 0, quarterRevenue: 0, cancelRate: 0 }
  gateways.value = gateways.value.map(g => ({ ...g, status: 'unknown', latency: null }))
}

async function exportReport () {
  // TODO: gọi API xuất báo cáo theo bộ lọc
  // await api.exportReport(filters)
  alert('Đang tạo & tải file báo cáo… (giao diện mẫu)')
}

/* ===== CÁC HÀM HỖ TRỢ ===== */
const fmtMoney = v =>
  typeof v === 'number' ? v.toLocaleString('vi-VN', { style: 'currency', currency: 'VND' }) : '—'

const formatDate = (dateStr) => {
  if (!dateStr) return '—'
  const date = new Date(dateStr)
  return date.toLocaleDateString('vi-VN')
}

const formatTime = (dateStr) => {
  if (!dateStr) return '—'
  const date = new Date(dateStr)
  return date.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' })
}

const getGatewayIcon = (code) => ({ momo: 'fas fa-mobile-alt', zalopay: 'fas fa-qrcode', paypal: 'fab fa-paypal', bank: 'fas fa-university' }[code] || 'fas fa-credit-card')
const getGatewayInitial = (code) => ({ momo: 'M', zalopay: 'Z', paypal: 'P' }[code] || (code ? code.charAt(0).toUpperCase() : '?'))
// Return Vite-resolvable image path for a gateway code
const getGatewayImage = (code) => {
  // map lowercase codes to files added under src/assets/image
  const map = { momo: '/src/assets/image/Momo.png', zalopay: '/src/assets/image/zalopay.png', paypal: '/src/assets/image/PayPal.png' }
  return map[code] || '/src/assets/image/logo.png'
}
const getGatewayClass = (gateway) => ({ 'Momo': 'gateway-momo', 'ZaloPay': 'gateway-zalopay', 'PayPal': 'gateway-paypal', 'Bank': 'gateway-bank' }[gateway] || 'gateway-default')
const getTransactionTypeClass = (type) => type === 'Hoàn tiền' ? 'transaction-type refund' : 'transaction-type payment'
const getTransactionTypeIcon = (type) => type === 'Hoàn tiền' ? 'fas fa-undo' : 'fas fa-arrow-down'
const getAmountClass = (type) => type === 'Hoàn tiền' ? 'amount refund' : 'amount payment'
const getStatusIcon = (status) => ({ 'Thành công': 'fas fa-check-circle', 'Đang chờ': 'fas fa-clock', 'Thất bại': 'fas fa-times-circle', 'Đã hoàn tiền': 'fas fa-undo' }[status] || 'fas fa-question-circle')

const gwStatusText = s => ({ up: 'Hoạt động', down: 'Sự cố' }[s] || 'Không rõ')
const gwStatusBadge = s => ({ up: 'badge dk-badge dk-badge-success', down: 'badge dk-badge dk-badge-danger' }[s] || 'badge dk-badge dk-badge-secondary')
const txnStatusBadge = s => ({ 'Thành công': 'badge dk-badge dk-badge-primary', 'Đang chờ': 'badge dk-badge dk-badge-warning', 'Thất bại': 'badge dk-badge dk-badge-danger', 'Đã hoàn tiền': 'badge dk-badge dk-badge-secondary' }[s] || 'badge dk-badge dk-badge-secondary')

// Các hàm thao tác
const checkGateways = () => console.log('Checking gateways...')
const refreshTransactions = () => { console.log('Refreshing transactions...'); fetchTransactions() }
const viewTransaction = (txn) => console.log('Viewing transaction:', txn.id)
const approveTransaction = (txn) => console.log('Approving transaction:', txn.id)
const rejectTransaction = (txn) => console.log('Rejecting transaction:', txn.id)

/* Phân trang */
function goPrev(){ if (page.value > 1) { page.value--; fetchTransactions() } }
function goNext(){ if (page.value < totalPages.value || totalPages.value === 0) return; page.value++; fetchTransactions() }
</script>

<style scoped>
.qltt-page{ padding:16px; background:#f5f7fb; min-height:100%; }

/* ===== PHẦN ĐẦU ===== */
.header-bar{ border-bottom:2px solid #eef2f6; padding-bottom:10px; }
.page-title{
  display:flex; align-items:center; gap:10px;
  font-size:1.6rem; font-weight:800; color:#0f172a; margin:0;
}

/* ===== NÚT BẤM (gradient đồng bộ) ===== */
.actions .btn{ border:0; color:#fff; border-radius:10px; font-weight:700; letter-spacing:.2px;
  box-shadow:0 2px 6px rgba(0,0,0,.08); transition:all .2s ease;
}
.actions .btn:hover{ transform:translateY(-1px); filter:brightness(1.06); }

.btn-grad-refresh{ background:linear-gradient(135deg,#00b4d8,#0096c7); }
.btn-grad-export { background:linear-gradient(135deg,#2f80ed,#56ccf2); }

/* ===== BỘ LỌC ===== */
.input-group-text{ background:#fff; border-right:none; color:#64748b; }
.form-control{ border-left:none; }
.form-control:focus,.form-select:focus{
  border-color:#93c5fd; box-shadow:0 0 0 .2rem rgba(147,197,253,.35);
}

/* ===== THẺ THỐNG KÊ ===== */
.kpi-grid{ display:grid; grid-template-columns:repeat(auto-fit,minmax(250px,1fr)); gap:1.5rem; margin-bottom:2rem; }
/* Reduce vertical spacing between filter -> KPI -> Gateway -> Transactions to bring controls closer */
.filters-row{ margin-bottom:0.5rem !important; }
.kpi-grid{ display:grid; grid-template-columns:repeat(auto-fit,minmax(250px,1fr)); gap:1.25rem; margin-bottom:1rem; }
.kpi-card{ background:linear-gradient(135deg,#fff 0%,#f8fafc 100%); border:1px solid #e2e8f0; border-radius:16px; padding:1.5rem; display:flex; align-items:center; gap:1rem; box-shadow:0 4px 6px -1px rgba(0,0,0,0.1); transition:all 0.3s ease; position:relative; overflow:hidden; }
.kpi-card::before{ content:''; position:absolute; top:0; left:0; right:0; height:4px; background:linear-gradient(90deg,var(--gradient-start),var(--gradient-end)); }
.kpi-card-primary{ --gradient-start:#3b82f6; --gradient-end:#1d4ed8; }
.kpi-card-success{ --gradient-start:#10b981; --gradient-end:#059669; }
.kpi-card-info{ --gradient-start:#06b6d4; --gradient-end:#0891b2; }
.kpi-card-warning{ --gradient-start:#f59e0b; --gradient-end:#d97706; }
.kpi-card:hover{ transform:translateY(-4px); box-shadow:0 20px 25px -5px rgba(0,0,0,0.1); }
.kpi-icon{ width:60px; height:60px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.5rem; color:#fff; background:linear-gradient(135deg,var(--gradient-start),var(--gradient-end)); box-shadow:0 4px 8px rgba(0,0,0,0.15); }
.kpi-content{ flex:1; }
.kpi-label{ color:#64748b; font-size:0.875rem; font-weight:500; margin-bottom:0.5rem; }
.kpi-value{ font-size:1.75rem; font-weight:800; color:#1e293b; margin-bottom:0.25rem; line-height:1; }
.kpi-trend{ display:flex; align-items:center; gap:0.25rem; font-size:0.75rem; font-weight:600; }

/* ===== PHẦN CỔNG THANH TOÁN ===== */
.gateway-section{ margin-bottom:1rem; }
.section-header{ display:flex; align-items:center; justify-content:space-between; margin-bottom:1rem; }
.section-title{ color:#1e293b; font-weight:700; font-size:1.1rem; display:flex; align-items:center; margin:0; }
.gateway-grid{ display:grid; grid-template-columns:repeat(auto-fit,minmax(300px,1fr)); gap:1rem; }
.gateway-card{ background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:1.25rem; box-shadow:0 1px 3px rgba(0,0,0,0.1); transition:all 0.3s ease; }
.gateway-card:hover{ transform:translateY(-2px); box-shadow:0 4px 12px rgba(0,0,0,0.15); }
.gateway-header{ display:flex; align-items:center; justify-content:space-between; margin-bottom:1rem; }
.gateway-icon{ width:48px; height:48px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:1rem; color:#fff; box-shadow:0 4px 8px rgba(0,0,0,0.08); }
.gateway-icon.momo{ background:linear-gradient(135deg,#ff8a65,#ff5252); }
.gateway-icon.zalopay{ background:linear-gradient(135deg,#60a5fa,#1d4ed8); }
.gateway-icon.paypal{ background:linear-gradient(135deg,#4dd0e1,#00838f); }
.gateway-icon.bank{ background:linear-gradient(135deg,#bbf7d0,#34d399); }
.gateway-icon.default{ background:linear-gradient(135deg,#c7cfd9,#94a3b8); }
.gateway-initial{ font-weight:800; font-size:1rem; line-height:1; }
.gateway-icon-img{ width:48px; height:48px; display:flex; align-items:center; justify-content:center; }
.gateway-img{ width:40px; height:40px; object-fit:contain; border-radius:8px; }
.gateway-status{ display:flex; align-items:center; gap:0.5rem; }
.status-indicator{ width:8px; height:8px; border-radius:50%; background:#6b7280; }
.status-indicator.up{ background:#10b981; box-shadow:0 0 0 2px rgba(16,185,129,0.2); }
.status-indicator.down{ background:#ef4444; box-shadow:0 0 0 2px rgba(239,68,68,0.2); }
.gateway-body{ text-align:left; }
.gateway-name{ font-weight:700; color:#1e293b; font-size:1rem; margin-bottom:0.75rem; }
.gateway-metrics{ display:flex; gap:1rem; }
.metric{ display:flex; flex-direction:column; gap:0.25rem; }
.metric-label{ font-size:0.75rem; color:#64748b; font-weight:500; }
.metric-value{ font-size:0.875rem; color:#1e293b; font-weight:600; }

/* ===== PHẦN GIAO DỊCH ===== */
.transaction-section{ margin-bottom:1rem; }
.transaction-stats{ display:flex; align-items:center; gap:0.5rem; }
.transaction-total{ display:flex; align-items:center; gap:0.5rem; background:linear-gradient(90deg,#eef2ff,#f0f9ff); padding:0.5rem 0.75rem; border-radius:12px; border:1px solid #e6eefc; }
.transaction-total .total-number{ font-size:1.25rem; font-weight:900; color:#0b3b6e; margin-right:0.5rem; }
.transaction-total .total-label{ font-size:0.85rem; color:#475569; font-weight:600; }
.stats-actions{ display:flex; align-items:center; gap:0.5rem; margin-left:0.75rem; }
.btn-refresh{ background:linear-gradient(135deg,#06b6d4,#3b82f6); color:#fff; border:0; padding:0.35rem 0.6rem; border-radius:8px; font-weight:700; }
.btn-refresh i{ opacity:0.9 }
.btn-check{ background:linear-gradient(135deg,#10b981,#059669); color:#fff; border:0; padding:0.35rem 0.6rem; border-radius:8px; font-weight:700; }
.btn-refresh:hover,.btn-check:hover{ transform:translateY(-2px); filter:brightness(1.02); }
.section-header .btn-check{ padding:0.35rem 0.7rem; font-weight:700 }

.transaction-total.zero{ background:linear-gradient(90deg,#fff6f6,#fff1f0); border:1px solid #fee2e2; }
.transaction-total.zero .total-number{ color:#c24124; font-size:1.6rem; }
.table-container{ background:#fff; border-radius:12px; border:1px solid #e2e8f0; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,0.1); }
.table-header{ background:linear-gradient(135deg,#f8fafc 0%,#f1f5f9 100%); border-bottom:2px solid #e2e8f0; }
.table-header th{ color:#374151; font-weight:700; font-size:0.875rem; text-transform:uppercase; letter-spacing:0.05em; padding:1rem 0.75rem; border:none; }
.transaction-row{ transition:all 0.2s ease; }
.transaction-row:hover{ background:#f8fafc; }
.transaction-id{ font-family:'Monaco','Menlo','Ubuntu Mono',monospace; font-weight:600; color:#3b82f6; font-size:0.875rem; }
.order-id{ font-family:'Monaco','Menlo','Ubuntu Mono',monospace; color:#6b7280; font-size:0.875rem; }
.transaction-type{ display:inline-flex; align-items:center; gap:0.375rem; padding:0.25rem 0.75rem; border-radius:9999px; font-size:0.75rem; font-weight:600; }
.transaction-type.payment{ background:#dcfce7; color:#166534; }
.transaction-type.refund{ background:#fee2e2; color:#991b1b; }
.amount{ font-weight:700; font-size:0.875rem; }
.amount.payment{ color:#059669; }
.amount.refund{ color:#dc2626; }
.gateway-badge{ display:inline-flex; align-items:center; gap:0.375rem; padding:0.25rem 0.75rem; border-radius:9999px; font-size:0.75rem; font-weight:600; background:#f1f5f9; color:#475569; }
.gateway-momo{ background:#fce7f3; color:#be185d; }
.gateway-zalopay{ background:#dbeafe; color:#1d4ed8; }
.gateway-paypal{ background:#dbeafe; color:#1e40af; }
.gateway-bank{ background:#dcfce7; color:#166534; }
.datetime{ font-size:0.875rem; }
.datetime .date{ font-weight:600; color:#1e293b; }
.datetime .time{ color:#64748b; font-size:0.75rem; }
.action-buttons{ display:flex; gap:0.25rem; justify-content:center; }
.action-buttons .btn{ width:32px; height:32px; padding:0; display:flex; align-items:center; justify-content:center; border-radius:6px; font-size:0.75rem; }
.empty-state{ padding:3rem 1rem; }
.empty-state i{ opacity:0.5; }

/* ===== BẢNG ===== */
.table th{ color:#0b3b6e; font-weight:700; white-space:nowrap; }
.text-primary-700{ color:#0a3563; }

/* Màu sắc badge */
.dk-badge{ padding:.45em .7em; min-width:90px; border-radius:999px; font-weight:700; text-align:center; }
.dk-badge-primary  { background:#e6eff8; color:#0b3b6e; }
.dk-badge-success  { background:#e7f6ee; color:#17864d; }
.dk-badge-danger   { background:#fde8e8; color:#c24124; }
.dk-badge-warning  { background:#fff3cd; color:#7a5c00; }
.dk-badge-secondary{ background:#eef2f6; color:#475569; }

/* Phân trang */
.page-link{ border-radius:8px !important; color:#0f172a; }
</style>
