<template>
  <div class="qltt-page">
    <div class="card p-4 shadow-sm rounded-3">

      <!-- ===== PHẦN ĐẦU TRANG ===== -->
      <div class="header-bar d-flex align-items-center justify-content-between flex-wrap mb-3">
        <h4 class="page-title m-0">
          <i class="fas fa-credit-card me-2"></i> Quản Lý Thanh Toán & Giao Dịch
        </h4>

        <div class="actions d-flex flex-wrap gap-2">
          <button
            type="button"
            class="btn btn-grad-refresh"
            :disabled="isLoading"
            @click="fetchTransactions"
          >
            <span v-if="isLoading" class="spinner-border spinner-border-sm me-1"></span>
            <i v-else class="fas fa-rotate me-1"></i> Tải lại
          </button>
          <button
            type="button"
            class="btn btn-grad-export"
            :disabled="exportLoading"
            @click="exportReport"
          >
            <span v-if="exportLoading" class="spinner-border spinner-border-sm me-1"></span>
            <i v-else class="fas fa-file-export me-1"></i> Xuất báo cáo
          </button>
        </div>
      </div>

      <!-- ===== BỘ LỌC ===== -->
      <div class="row g-2 align-items-stretch mb-3 filters-row">
        <div class="col-12 col-lg-3">
          <select class="form-select h-100" v-model="filters.gateway" @change="fetchTransactions">
            <option value="">— Cổng thanh toán —</option>
            <option value="momo">Momo</option>
            <option value="bank">Ngân hàng</option>
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
        <div v-if="kpiError" class="alert alert-warning py-2 px-3 mb-3">
          <i class="fas fa-circle-exclamation me-2"></i>{{ kpiError }}
        </div>
        <div class="kpi-grid">
          <div class="kpi-card kpi-card-primary">
            <div class="kpi-icon"><i class="fas fa-calendar-day"></i></div>
            <div class="kpi-content">
              <div class="kpi-label">Doanh thu hôm nay</div>
              <div class="kpi-value">{{ fmtMoney(kpi.todayRevenue) }}</div>
            </div>
          </div>

          <div class="kpi-card kpi-card-success">
            <div class="kpi-icon"><i class="fas fa-calendar-alt"></i></div>
            <div class="kpi-content">
              <div class="kpi-label">Doanh thu tháng này</div>
              <div class="kpi-value">{{ fmtMoney(kpi.monthRevenue) }}</div>
            </div>
          </div>

          <div class="kpi-card kpi-card-info">
            <div class="kpi-icon"><i class="fas fa-chart-line"></i></div>
            <div class="kpi-content">
              <div class="kpi-label">Doanh thu quý này</div>
              <div class="kpi-value">{{ fmtMoney(kpi.quarterRevenue) }}</div>
            </div>
          </div>

          <div class="kpi-card kpi-card-warning">
            <div class="kpi-icon"><i class="fas fa-exclamation-triangle"></i></div>
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

          <!-- Một nút Quản lý mở popup -->
          <button class="btn btn-sm btn-manage" @click="openManage">
            <i class="fas fa-sliders-h me-1"></i> Quản lý
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

      <!-- ===== POPUP QUẢN LÝ CỔNG ===== -->
      <div v-if="showManage" class="dk-modal-backdrop" @click.self="closeManage">
        <div class="dk-modal-card" role="dialog" aria-modal="true">
          <div class="dk-modal-header">
            <h6 class="m-0"><i class="fas fa-sliders-h me-2"></i>Quản lý cổng thanh toán</h6>
            <button class="btn btn-close-x" @click="closeManage" aria-label="Đóng">
              <i class="fas fa-times"></i>
            </button>
          </div>

          <div class="dk-modal-body">
            <!-- Momo -->
            <div class="manage-block">
              <div class="manage-left">
                <img
                  class="qr-img"
                  src="/src/assets/image/qrMomo.png"
                  alt="QR Momo 0366818392"
                />
              </div>
              <div class="manage-right">
                <div class="manage-title">
                  <img class="small-logo" src="/src/assets/image/Momo.png" alt="Momo" />
                  <strong>Momo</strong>
                </div>
                <div class="manage-row">
                  <span class="manage-label">Mã ví:</span>
                  <span class="manage-code">{{ gatewaySettings.momo.code }}</span>
                </div>
                <div class="form-check form-switch mt-2">
                  <input
                    class="form-check-input"
                    type="checkbox"
                    id="swMomo"
                    v-model="gatewaySettings.momo.enabled"
                    @change="applyGatewayToggles"
                  >
                  <label class="form-check-label" for="swMomo">
                    {{ gatewaySettings.momo.enabled ? 'Đang hoạt động' : 'Tắt' }}
                  </label>
                </div>
              </div>
            </div>

            <!-- Ngân hàng -->
            <div class="manage-block">
              <div class="manage-left">
                <img
                  class="qr-img"
                  :src="vietQrUrl"
                  alt="VietQR HO TRONG KIM"
                />
              </div>
              <div class="manage-right">
                <div class="manage-title">
                  <img
                    class="small-logo"
                    src="https://cdn-icons-png.flaticon.com/512/3208/3208667.png"
                    alt="Ngân hàng"
                  />
                  <strong>Ngân hàng (VCB)</strong>
                </div>
                <div class="manage-row">
                  <span class="manage-label">Số TK:</span>
                  <span class="manage-code">{{ gatewaySettings.bank.code }}</span>
                </div>
                <div class="form-check form-switch mt-2">
                  <input
                    class="form-check-input"
                    type="checkbox"
                    id="swBank"
                    v-model="gatewaySettings.bank.enabled"
                    @change="applyGatewayToggles"
                  >
                  <label class="form-check-label" for="swBank">
                    {{ gatewaySettings.bank.enabled ? 'Đang hoạt động' : 'Tắt' }}
                  </label>
                </div>
              </div>
            </div>
          </div>

          <div class="dk-modal-footer">
            <button class="btn btn-light" @click="closeManage">Đóng</button>
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
      <!-- ===== DANH SÁCH GIAO DỊCH ===== -->
            </div>
            <div class="stats-actions">
              <button class="btn btn-sm btn-refresh me-2" :disabled="isLoading" @click="refreshTransactions">
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
                </tr>
              </thead>

            <tbody v-if="isLoading">
              <tr>
                <td colspan="8" class="text-center py-5">
                  <div class="d-flex flex-column align-items-center gap-2 text-muted">
                    <div class="spinner-border text-primary" role="status"></div>
                    <div>Đang tải giao dịch...</div>
                  </div>
                </td>
              </tr>
            </tbody>

            <tbody v-else-if="tableError">
              <tr>
                <td colspan="8" class="text-center text-danger py-4">
                  <i class="fas fa-triangle-exclamation me-2"></i>{{ tableError }}
                </td>
              </tr>
            </tbody>

            <tbody v-else-if="!hasTransactions">
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
              <tr v-for="(txn, i) in visibleTransactions" :key="txn.id || i" class="transaction-row">
                <td class="text-center">{{ (page - 1) * pageSize + i + 1 }}</td>
                <td><span class="transaction-id">{{ txn.id || '—' }}</span></td>
                <td><span class="order-id">#{{ txn.orderId || '—' }}</span></td>
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
                </tr>
              </tbody>

            </table>
          </div>
        </div>
      </div>

      <!-- ===== PHẦN TRANG ===== -->
      <div class="d-flex justify-content-between align-items-center">
        <small class="text-muted">Trang {{ page }}/{{ totalPages || 1 }}</small>
        <ul class="pagination mb-0">
          <li :class="['page-item', {disabled: page===1}]">
            <button class="page-link" @click="goPrev">«</button>
          </li>
          <li class="page-item disabled"><span class="page-link">{{ page }}</span></li>
          <li :class="['page-item', {disabled: totalPages===0 || page===totalPages}]">
            <button class="page-link" @click="goNext">»</button>
          </li>
        </ul>
      </div>

      <!-- ===== GHI CHÚ & HƯỚNG DẪN ===== -->
      <div class="alert alert-info mt-3 mb-0">
        <ul class="mb-0">
          <li>Kết nối & giám sát cổng thanh toán: Momo, Ngân hàng.</li>
          <li>Xem lịch sử giao dịch; tổng doanh thu theo ngày / tháng / quý.</li>
          <li>Xuất báo cáo tài chính; thống kê doanh thu và tỉ lệ hủy vé.</li>
        </ul>
      </div>

    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import api from '../../../services/api'

const filters = reactive({
  gateway: '',
  type: '',
  dateFrom: '',
  dateTo: '',
  keyword: '',
})

const page = ref(1)
const pageSize = ref(10)
const total = ref(0)
const lastPage = ref(1)

const transactions = ref([])
const tableError = ref('')
const isLoading = ref(false)
const exportLoading = ref(false)

const visibleTransactions = computed(() => {
  const keyword = filters.keyword.trim().toLowerCase()
  if (!keyword) {
    return transactions.value
  }
  return transactions.value.filter((txn) => {
    const haystack = `${txn.id ?? ''} ${txn.orderId ?? ''}`.toLowerCase()
    return haystack.includes(keyword)
  })
})
const hasTransactions = computed(() => visibleTransactions.value.length > 0)
const totalPages = computed(() => Math.max(1, lastPage.value || 1))

const kpi = reactive({
  todayRevenue: 0,
  monthRevenue: 0,
  quarterRevenue: 0,
  cancelRate: 0,
})
const kpiError = ref('')
const kpiLoading = ref(false)

const gatewaySettings = reactive({
  momo: { enabled: true, code: '0366818392' },
  bank: { enabled: true, code: '1037240068' },
})

const gateways = ref([
  { code: 'momo', name: 'Momo', status: 'unknown', latency: null, uptime: null, amount: 0, count: 0 },
  { code: 'bank', name: 'Ngân hàng', status: 'unknown', latency: null, uptime: null, amount: 0, count: 0 },
])

const statusMap = {
  'Thanh toán': 'success',
  'Hoàn tiền': 'refunded',
}
const gatewayTypeMap = {
  momo: { type: 'online', method: 'QR' },
  bank: { type: 'manual', method: undefined },
}

const fmtMoney = (value) =>
  typeof value === 'number'
    ? value.toLocaleString('vi-VN', { style: 'currency', currency: 'VND' })
    : '—'

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

const getGatewayIcon = (code) =>
  ({ momo: 'fas fa-mobile-alt', bank: 'fas fa-university' }[code] || 'fas fa-credit-card')

const getGatewayImage = (code) => {
  const map = {
    momo: '/src/assets/image/Momo.png',
    bank: 'https://cdn-icons-png.flaticon.com/512/3208/3208667.png',
  }
  return map[code] || '/src/assets/image/logo.png'
}

const getGatewayClass = (gateway) =>
  ({ Momo: 'gateway-momo', 'Ngân hàng': 'gateway-bank' }[gateway] || 'gateway-default')

const getTransactionTypeClass = (type) =>
  type === 'Hoàn tiền' ? 'transaction-type refund' : 'transaction-type payment'
const getTransactionTypeIcon = (type) => (type === 'Hoàn tiền' ? 'fas fa-undo' : 'fas fa-arrow-down')
const getAmountClass = (type) => (type === 'Hoàn tiền' ? 'amount refund' : 'amount payment')
const getStatusIcon = (status) =>
  ({
    'Thành công': 'fas fa-check-circle',
    'Đang chờ': 'fas fa-clock',
    'Thất bại': 'fas fa-times-circle',
    'Đã hoàn': 'fas fa-undo',
    'Đã hoàn tiền': 'fas fa-undo',
    'Hoàn tiền': 'fas fa-undo',
  }[status] || 'fas fa-question-circle')

const gwStatusText = (status) => ({ up: 'Hoạt động', down: 'Sự cố', unknown: 'Không rõ' }[status] || 'Không rõ')
const gwStatusBadge = (status) =>
  ({
    up: 'badge dk-badge dk-badge-success',
    down: 'badge dk-badge dk-badge-danger',
    unknown: 'badge dk-badge dk-badge-secondary',
  }[status] || 'badge dk-badge dk-badge-secondary')
const txnStatusBadge = (status) =>
  ({
    'Thành công': 'badge dk-badge dk-badge-primary',
    'Đang chờ': 'badge dk-badge dk-badge-warning',
    'Thất bại': 'badge dk-badge dk-badge-danger',
    'Đã hoàn': 'badge dk-badge dk-badge-secondary',
    'Đã hoàn tiền': 'badge dk-badge dk-badge-secondary',
    'Hoàn tiền': 'badge dk-badge dk-badge-secondary',
  }[status] || 'badge dk-badge dk-badge-secondary')

const checkGateways = () => {
  gateways.value = gateways.value.map((gateway) => ({
    ...gateway,
    latency: gateway.status === 'up' ? Math.max(20, Math.round(Math.random() * 40)) : null,
  }))
}

const refreshTransactions = () => fetchTransactions()
const viewTransaction = (txn) => console.log('Viewing transaction:', txn?.id)
const approveTransaction = (txn) => console.log('Approving transaction:', txn?.id)
const rejectTransaction = (txn) => console.log('Rejecting transaction:', txn?.id)

const showManage = ref(false)
const openManage = () => (showManage.value = true)
const closeManage = () => (showManage.value = false)

const vietQrUrl = computed(
  () =>
    'https://img.vietqr.io/image/VCB-1037240068-compact.png?amount=150000&addInfo=Thanh+toan+ve+xe+DKMN'
)

const goPrev = () => {
  if (page.value > 1) {
    page.value -= 1
    fetchTransactions()
  }
}
const goNext = () => {
  if (page.value < totalPages.value) {
    page.value += 1
    fetchTransactions()
  }
}

const normalizeGatewayLabel = (provider = '', method = '') => {
  const normalized = `${provider} ${method}`.toLowerCase()
  if (normalized.includes('momo') || method === 'QR') {
    return { label: 'Momo', code: 'momo' }
  }
  if (normalized.includes('bank') || normalized.includes('vcb') || provider === 'admin_manual') {
    return { label: 'Ngân hàng', code: 'bank' }
  }
  return { label: provider || 'Khác', code: 'other' }
}

const normalizeTransaction = (row) => {
  const { label, code } = normalizeGatewayLabel(row.provider, row.method)
  const isRefund =
    row.status?.toLowerCase() === 'refunded' ||
    (row.statusLabel && row.statusLabel.toLowerCase().includes('hoàn'))
  return {
    id: row.id,
    orderId: row.orderId,
    amount: Number(row.amount) || 0,
    gateway: label,
    gatewayCode: code,
    status: row.statusLabel || 'Đang chờ',
    type: isRefund ? 'Hoàn tiền' : 'Thanh toán',
    time: row.paidAt,
  }
}

const applyGatewayToggles = (list = transactions.value) => {
  const summary = list.reduce(
    (acc, txn) => {
      if (!txn.gatewayCode || !acc[txn.gatewayCode]) {
        return acc
      }
      acc[txn.gatewayCode].amount += txn.amount || 0
      acc[txn.gatewayCode].count += 1
      return acc
    },
    {
      momo: { amount: 0, count: 0 },
      bank: { amount: 0, count: 0 },
    }
  )

  gateways.value = gateways.value.map((gateway) => {
    const toggledOn = gateway.code === 'momo' ? gatewaySettings.momo.enabled : gatewaySettings.bank.enabled
    return {
      ...gateway,
      status: toggledOn ? 'up' : 'down',
      latency: toggledOn ? Math.max(20, Math.round(Math.random() * 40)) : null,
      uptime: toggledOn ? 99.9 : null,
      amount: summary[gateway.code]?.amount || 0,
      count: summary[gateway.code]?.count || 0,
    }
  })
}

const fetchTransactions = async () => {
  isLoading.value = true
  tableError.value = ''
  const params = {
    page: page.value,
    perPage: pageSize.value,
    dateFrom: filters.dateFrom || undefined,
    dateTo: filters.dateTo || undefined,
    status: statusMap[filters.type] || undefined,
  }
  const gatewayFilter = gatewayTypeMap[filters.gateway]
  if (gatewayFilter?.type) params.type = gatewayFilter.type
  if (gatewayFilter?.method) params.method = gatewayFilter.method

  try {
    const { data } = await api.get('/admin/payments', { params })
    const list = Array.isArray(data?.data) ? data.data.map(normalizeTransaction) : []
    transactions.value = list
    const meta = data?.meta || {}
    total.value = meta.total ?? list.length
    lastPage.value =
      (meta.lastPage ?? Math.ceil((total.value || list.length) / pageSize.value)) || 1
    page.value = meta.currentPage ?? page.value
    applyGatewayToggles(list)
  } catch (error) {
    const errorMsg = error?.response?.data?.message || error?.message || 'Không thể tải danh sách giao dịch.'
    tableError.value = errorMsg
    window.$toast?.error?.(errorMsg)
    transactions.value = []
    total.value = 0
    lastPage.value = 1
    applyGatewayToggles([])
  } finally {
    isLoading.value = false
  }
}

const loadKpi = async () => {
  kpiLoading.value = true
  kpiError.value = ''
  try {
    const [overview, month, quarter] = await Promise.all([
      api.get('/admin/statistics/overview'),
      api.get('/admin/statistics/report', { params: { period: 'month' } }),
      api.get('/admin/statistics/report', { params: { period: 'quarter' } }),
    ])
    const summary = overview?.data?.data?.summary || {}
    kpi.todayRevenue = summary.revenueToday || 0
    kpi.monthRevenue = month?.data?.data?.totalRevenue || 0
    kpi.quarterRevenue = quarter?.data?.data?.totalRevenue || 0
    kpi.cancelRate = month?.data?.data?.cancellationRate || 0
  } catch (error) {
    const errorMsg = error?.response?.data?.message || error?.message || 'Không thể tải thống kê.'
    kpiError.value = errorMsg
    window.$toast?.error?.(errorMsg)
    kpi.todayRevenue = 0
    kpi.monthRevenue = 0
    kpi.quarterRevenue = 0
    kpi.cancelRate = 0
  } finally {
    kpiLoading.value = false
  }
}

const exportReport = async () => {
  exportLoading.value = true
  window.$toast?.info?.('Đang tạo báo cáo...')
  try {
    const params = {
      ...gatewayTypeMap[filters.gateway],
      status: statusMap[filters.type] || undefined,
      dateFrom: filters.dateFrom || undefined,
      dateTo: filters.dateTo || undefined,
      limit: 5000,
    }
    const response = await api.get('/admin/payments/export', {
      params,
      responseType: 'blob',
      headers: {
        Accept: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
      },
    })
    const blob = new Blob([response.data], {
      type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    })
    const disposition = response.headers?.['content-disposition'] || ''
    const match = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/i.exec(disposition)
    let filename = match ? match[1] : ''
    if (filename) {
      filename = filename.replace(/"/g, '')
    } else {
      const stamp = new Date().toISOString().replace(/[-:]/g, '').slice(0, 15)
      filename = `dkmn-payments-${stamp}.xlsx`
    }
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.download = filename
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    window.URL.revokeObjectURL(url)
    window.$toast?.success?.('Xuất báo cáo thành công! 📊')
  } catch (error) {
    let message = 'Không thể xuất báo cáo.'
    const blob = error?.response?.data
    if (blob instanceof Blob) {
      try {
        const text = await blob.text()
        const parsed = JSON.parse(text)
        message = parsed?.message || message
      } catch (err) {
        console.warn('cannot parse export error', err)
      }
    } else if (error?.response?.data?.message) {
      message = error.response.data.message
    } else if (error?.message) {
      message = error.message
    }
    window.$toast?.error?.(message)
    alert(message)
  } finally {
    exportLoading.value = false
  }
}

onMounted(() => {
  applyGatewayToggles([])
  loadKpi()
  fetchTransactions()
})
</script>

<style scoped>
.qltt-page{ padding:16px; background:#f5f7fb; min-height:100%; }

/* ===== PHẦN ĐẦU ===== */
.header-bar{ border-bottom:2px solid #eef2f6; padding-bottom:10px; }
.page-title{ display:flex; align-items:center; gap:10px; font-size:1.6rem; font-weight:800; color:#0f172a; margin:0; }

/* ===== NÚT BẤM ===== */
.actions .btn{ border:0; color:#fff; border-radius:10px; font-weight:700; letter-spacing:.2px;
  box-shadow:0 2px 6px rgba(0,0,0,.08); transition:all .2s ease; }
.actions .btn:hover{ transform:translateY(-1px); filter:brightness(1.06); }
.btn-grad-refresh{ background:linear-gradient(135deg,#00b4d8,#0096c7); }
.btn-grad-export { background:linear-gradient(135deg,#2f80ed,#56ccf2); }

/* ===== BỘ LỌC ===== */
.input-group-text{ background:#fff; border-right:none; color:#64748b; }
.form-control{ border-left:none; }
.form-control:focus,.form-select:focus{ border-color:#93c5fd; box-shadow:0 0 0 .2rem rgba(147,197,253,.35); }

/* ===== THẺ THỐNG KÊ ===== */
.filters-row{ margin-bottom:0.5rem !important; }
.kpi-grid{ display:grid; grid-template-columns:repeat(auto-fit,minmax(250px,1fr)); gap:1.25rem; margin-bottom:1rem; }
.kpi-card{ background:linear-gradient(135deg,#fff 0%,#f8fafc 100%); border:1px solid #e2e8f0; border-radius:16px; padding:1.5rem; display:flex; align-items:center; gap:1rem; box-shadow:0 4px 6px -1px rgba(0,0,0,0.1); transition:all .3s ease; position:relative; overflow:hidden; }
.kpi-card::before{ content:''; position:absolute; top:0; left:0; right:0; height:4px; background:linear-gradient(90deg,var(--gradient-start),var(--gradient-end)); }
.kpi-card-primary{ --gradient-start:#3b82f6; --gradient-end:#1d4ed8; }
.kpi-card-success{ --gradient-start:#10b981; --gradient-end:#059669; }
.kpi-card-info{ --gradient-start:#06b6d4; --gradient-end:#0891b2; }
.kpi-card-warning{ --gradient-start:#f59e0b; --gradient-end:#d97706; }
.kpi-icon{ width:60px; height:60px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.5rem; color:#fff; background:linear-gradient(135deg,var(--gradient-start),var(--gradient-end)); box-shadow:0 4px 8px rgba(0,0,0,.15); }
.kpi-label{ color:#64748b; font-size:.875rem; font-weight:500; margin-bottom:.5rem; }
.kpi-value{ font-size:1.75rem; font-weight:800; color:#1e293b; }

/* ===== PHẦN CỔNG ===== */
.gateway-section{ margin-bottom:1rem; }
.section-header{ display:flex; align-items:center; justify-content:space-between; gap:1rem; margin-bottom:1rem; flex-wrap:wrap; }
.section-title{ color:#1e293b; font-weight:700; font-size:1.1rem; display:flex; align-items:center; margin:0; }
.btn-manage{
  background:linear-gradient(135deg,#6366f1,#22d3ee);
  color:#fff; border:0; padding:.4rem .75rem; border-radius:10px; font-weight:700;
}
.gateway-grid{ display:grid; grid-template-columns:repeat(auto-fit,minmax(300px,1fr)); gap:1rem; }
.gateway-card{ background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:1.25rem; box-shadow:0 1px 3px rgba(0,0,0,0.1); transition:all .3s ease; }
.gateway-card:hover{ transform:translateY(-2px); box-shadow:0 4px 12px rgba(0,0,0,0.15); }
.gateway-header{ display:flex; align-items:center; justify-content:space-between; margin-bottom:1rem; }
.gateway-icon-img{ width:48px; height:48px; display:flex; align-items:center; justify-content:center; }
.gateway-img{ width:40px; height:40px; object-fit:contain; border-radius:8px; }
.gateway-status{ display:flex; align-items:center; gap:.5rem; }
.status-indicator{ width:8px; height:8px; border-radius:50%; background:#6b7280; }
.status-indicator.up{ background:#10b981; box-shadow:0 0 0 2px rgba(16,185,129,0.2); }
.status-indicator.down{ background:#ef4444; box-shadow:0 0 0 2px rgba(239,68,68,0.2); }
.gateway-name{ font-weight:700; color:#1e293b; font-size:1rem; margin-bottom:.75rem; }
.gateway-metrics{ display:flex; gap:1rem; }
.metric-label{ font-size:.75rem; color:#64748b; font-weight:500; }
.metric-value{ font-size:.875rem; color:#1e293b; font-weight:600; }

/* ===== MODAL QUẢN LÝ ===== */
.dk-modal-backdrop{
  position:fixed; inset:0; background:rgba(15,23,42,.35);
  display:flex; align-items:center; justify-content:center; z-index:1050;
}
.dk-modal-card{
  width:min(760px,96vw); background:#fff; border-radius:16px;
  box-shadow:0 20px 40px rgba(0,0,0,.25); overflow:hidden;
}
.dk-modal-header, .dk-modal-footer{
  padding:.75rem 1rem; background:#f8fafc; border-bottom:1px solid #e2e8f0;
}
.dk-modal-footer{ border-top:1px solid #e2e8f0; border-bottom:none; }
.dk-modal-body{ padding:1rem; display:flex; flex-direction:column; gap:1rem; }
.btn-close-x{
  border:0; background:#fff; width:34px; height:34px; border-radius:8px;
  display:flex; align-items:center; justify-content:center; color:#0f172a;
  box-shadow:0 1px 3px rgba(0,0,0,.1);
}
.btn-close-x:hover{ filter:brightness(0.98); }

.manage-block{
  display:grid; grid-template-columns:120px 1fr; gap:1rem;
  border:1px solid #e2e8f0; border-radius:12px; padding:.75rem; background:#fff;
}
.manage-left{ display:flex; align-items:center; justify-content:center; }
.qr-img{ width:110px; height:110px; object-fit:contain; border-radius:10px; border:1px solid #e2e8f0; }
.manage-right{ display:flex; flex-direction:column; justify-content:center; gap:.25rem; }
.manage-title{ display:flex; align-items:center; gap:.5rem; font-size:1rem; color:#0f172a; }
.small-logo{ width:22px; height:22px; object-fit:contain; }
.manage-row{ display:flex; align-items:center; gap:.5rem; }
.manage-label{ color:#475569; font-weight:600; }
.manage-code{ font-family:'Monaco','Menlo','Ubuntu Mono',monospace; color:#0f766e; font-weight:700; }

@media (max-width: 576px){
  .manage-block{ grid-template-columns:1fr; }
  .qr-img{ width:140px; height:140px; }
}

/* ===== TRANSACTIONS ===== */
.transaction-section{ margin-bottom:1rem; }
.transaction-stats{ display:flex; align-items:center; gap:.5rem; }
.transaction-total{ display:flex; align-items:center; gap:.5rem; background:linear-gradient(90deg,#eef2ff,#f0f9ff); padding:.5rem .75rem; border-radius:12px; border:1px solid #e6eefc; }
.transaction-total .total-number{ font-size:1.25rem; font-weight:900; color:#0b3b6e; margin-right:.5rem; }
.transaction-total .total-label{ font-size:.85rem; color:#475569; font-weight:600; }
.stats-actions{ display:flex; align-items:center; gap:.5rem; margin-left:.75rem; }
.btn-refresh{ background:linear-gradient(135deg,#06b6d4,#3b82f6); color:#fff; border:0; padding:.35rem .6rem; border-radius:8px; font-weight:700; }
.btn-check{ background:linear-gradient(135deg,#10b981,#059669); color:#fff; border:0; padding:.35rem .6rem; border-radius:8px; font-weight:700; }
.btn-refresh:hover,.btn-check:hover{ transform:translateY(-2px); filter:brightness(1.02); }
.section-header .btn-check{ padding:.35rem .7rem; font-weight:700 }
.transaction-total.zero{ background:linear-gradient(90deg,#fff6f6,#fff1f0); border:1px solid #fee2e2; }
.transaction-total.zero .total-number{ color:#c24124; font-size:1.6rem; }
.table-container{ background:#fff; border-radius:12px; border:1px solid #e2e8f0; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,0.1); }
.table-header{ background:linear-gradient(135deg,#f8fafc 0%,#f1f5f9 100%); border-bottom:2px solid #e2e8f0; }
.table-header th{ color:#374151; font-weight:700; font-size:.875rem; text-transform:uppercase; letter-spacing:.05em; padding:1rem .75rem; border:none; }
.transaction-row{ transition:all .2s ease; }
.transaction-row:hover{ background:#f8fafc; }
.transaction-id{ font-family:'Monaco','Menlo','Ubuntu Mono',monospace; font-weight:600; color:#3b82f6; font-size:.875rem; }
.order-id{ font-family:'Monaco','Menlo','Ubuntu Mono',monospace; color:#6b7280; font-size:.875rem; }
.transaction-type{ display:inline-flex; align-items:center; gap:.375rem; padding:.25rem .75rem; border-radius:9999px; font-size:.75rem; font-weight:600; }
.transaction-type.payment{ background:#dcfce7; color:#166534; }
.transaction-type.refund{ background:#fee2e2; color:#991b1b; }
.amount{ font-weight:700; font-size:.875rem; }
.amount.payment{ color:#059669; }
.amount.refund{ color:#dc2626; }
.gateway-badge{ display:inline-flex; align-items:center; gap:.375rem; padding:.25rem .75rem; border-radius:9999px; font-size:.75rem; font-weight:600; background:#f1f5f9; color:#475569; }
.gateway-momo{ background:#fce7f3; color:#be185d; }
.gateway-bank{ background:#dcfce7; color:#166534; }
.datetime{ font-size:.875rem; }
.datetime .date{ font-weight:600; color:#1e293b; }
.datetime .time{ color:#64748b; font-size:.75rem; }
.empty-state{ padding:3rem 1rem; }
.table th{ color:#0b3b6e; font-weight:700; white-space:nowrap; }
.dk-badge{ padding:.45em .7em; min-width:90px; border-radius:999px; font-weight:700; text-align:center; }
.dk-badge-primary{ background:#e6eff8; color:#0b3b6e; }
.dk-badge-success{ background:#e7f6ee; color:#17864d; }
.dk-badge-danger{ background:#fde8e8; color:#c24124; }
.dk-badge-warning{ background:#fff3cd; color:#7a5c00; }
.dk-badge-secondary{ background:#eef2f6; color:#475569; }
.page-link{ border-radius:8px !important; color:#0f172a; }
</style>
