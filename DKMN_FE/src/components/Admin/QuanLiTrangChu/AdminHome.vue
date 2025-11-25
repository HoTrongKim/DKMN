<template>
  <div class="admin-dashboard">
    <div class="card p-4 shadow-sm position-relative">
      <div class="header-bar d-flex align-items-center justify-content-between mb-3">
        <h4 class="page-title m-0 text-dark">
          <i class="fas fa-chart-line me-2"></i> Bảng điều khiển
        </h4>
        <div class="d-flex align-items-center gap-2">
          <small class="text-muted" v-if="lastUpdated">Cập nhật: {{ formatDate(lastUpdated) }}</small>
          <button class="btn btn-light btn-sm" @click="loadDashboard" :disabled="loading">
            <i class="fas fa-rotate me-1"></i> Làm mới
          </button>
        </div>
      </div>

      <div class="stats-grid">
        <div class="stat-card blue">
          <div class="icon-box"><i class="fas fa-ticket-alt"></i></div>
          <div class="info">
            <h5>Vé bán hôm nay</h5>
            <p>{{ formatNumber(summary.ticketsToday) }}</p>
            <small>so với hôm qua: <span :class="trendClass(summary.ticketsTodayDelta)">
              {{ formatTrend(summary.ticketsTodayDelta) }}
            </span></small>
          </div>
        </div>
        <div class="stat-card green">
          <div class="icon-box"><i class="fas fa-sack-dollar"></i></div>
          <div class="info">
            <h5>Doanh thu hôm nay</h5>
            <p>{{ formatCurrency(summary.revenueToday) }}</p>
            <small>Δ {{ formatTrend(summary.revenueTodayDelta) }}</small>
          </div>
        </div>
        <div class="stat-card purple">
          <div class="icon-box"><i class="fas fa-user-plus"></i></div>
          <div class="info">
            <h5>Khách mới (7 ngày)</h5>
            <p>{{ formatNumber(summary.newCustomers) }}</p>
            <small>Tổng khách: {{ formatNumber(counters.customers) }}</small>
          </div>
        </div>
        <div class="stat-card orange">
          <div class="icon-box"><i class="fas fa-star"></i></div>
          <div class="info">
            <h5>Điểm đánh giá TB</h5>
            <p>{{ (summary.ratingScore || 0).toFixed(1) }}</p>
            <small>Trên {{ summary.ratingBase || 5 }} điểm</small>
          </div>
        </div>
      </div>

      <div class="dashboard-content">
        <div class="chart-box flex-grow-1">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i> Doanh thu 6 tháng gần nhất</h5>
          </div>
          <div v-if="monthlyRevenue.length" class="revenue-list">
            <div class="revenue-row" v-for="item in monthlyRevenue" :key="item.month">
              <div class="revenue-label">
                <strong>{{ formatMonth(item.month) }}</strong>
                <span>{{ formatCurrency(item.total) }}</span>
              </div>
              <div class="revenue-bar">
                <span
                  class="revenue-bar-inner"
                  :style="{ width: computeRevenueWidth(item.total) }"
                ></span>
              </div>
            </div>
          </div>
          <div v-else class="empty-box">Chưa có dữ liệu hiển thị</div>
        </div>
        <div class="activity-box">
          <h5><i class="fas fa-clock me-2"></i> Hoạt động gần đây</h5>
          <ul v-if="recentOrders.length" class="activity-list">
            <li v-for="order in recentOrders" :key="order.id">
              <div class="order-title">
                <div>
                  <strong>#{{ order.code }}</strong>
                  <span class="badge status-badge">{{ mapStatus(order.status) }}</span>
                </div>
                <span>{{ formatCurrency(order.total) }}</span>
              </div>
              <div class="order-meta">
                <span><i class="bx bx-user"></i> {{ order.customer }}</span>
                <span><i class="bx bx-time-five"></i> {{ formatDate(order.createdAt) }}</span>
              </div>
            </li>
          </ul>
          <div v-else class="empty-box">Chưa có dữ liệu hiển thị</div>

          <div class="top-routes" v-if="topRoutes.length">
            <h6 class="mt-4 mb-2"><i class="fas fa-route me-1"></i> Tuyến được đặt nhiều</h6>
            <ul>
              <li v-for="route in topRoutes" :key="route.route">
                <span>{{ route.route }}</span>
                <strong>{{ route.total }}</strong>
              </li>
            </ul>
          </div>
        </div>
      </div>

      <div class="notification-panel mt-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
          <div>
            <h5 class="mb-1"><i class="fas fa-bell me-2"></i> Gửi thông báo cho khách</h5>
            <small class="text-muted">Nhập tiêu đề, nội dung và gửi tới khách hàng (để trống email để gửi tất cả).</small>
          </div>
          <button class="btn btn-light btn-sm" @click="loadAdminNotifications" :disabled="notifyLoading">
            <i class="fas fa-rotate me-1"></i> Làm mới
          </button>
        </div>

        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label fw-semibold">Tiêu đề</label>
            <input class="form-control" v-model.trim="notifyForm.title" placeholder="VD: Thông báo lịch khởi hành" />
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Loại thông báo</label>
            <select class="form-select" v-model="notifyForm.type">
              <option value="info">Thông tin</option>
              <option value="success">Thành công</option>
              <option value="warning">Cảnh báo</option>
              <option value="error">Lỗi</option>
              <option value="inbox">Hộp thư đến</option>
              <option value="trip_update">Cập nhật chuyến</option>
            </select>
          </div>
          <div class="col-12">
            <label class="form-label fw-semibold">Nội dung</label>
            <textarea
              class="form-control"
              rows="3"
              v-model.trim="notifyForm.message"
              placeholder="Nhập nội dung muốn gửi cho khách hàng"
            ></textarea>
          </div>
          <div class="col-12">
            <label class="form-label fw-semibold">Khách hàng</label>
            <div class="customer-select-wrapper">
              <div class="d-flex justify-content-between align-items-center mb-1">
                <small class="text-muted">
                  Giữ Ctrl (hoặc Command) để chọn nhiều khách.
                </small>
                <small class="text-muted">{{ selectedCustomerIds.length }} đã chọn</small>
              </div>
              <select
                class="form-select customer-multi"
                multiple
                size="6"
                v-model="selectedCustomerIds"
              >
                <option
                  v-for="option in customerOptions"
                  :key="option.id"
                  :value="option.id"
                >
                  {{ option.label }}
                </option>
                <option v-if="!customerOptions.length" disabled>Đang tải danh sách khách...</option>
              </select>
            </div>
            <div class="mt-2">
              <label class="form-label fw-semibold">Email khách hàng (tùy chọn)</label>
              <input
                class="form-control"
                v-model="notifyForm.emails"
                placeholder="Nhập thêm email, ngăn cách nhiều email bằng dấu phẩy"
              />
              <small class="text-muted">Để trống để gửi cho tất cả khách hàng đang hoạt động.</small>
            </div>
          </div>
          <div class="col-12 d-flex justify-content-end">
            <button class="btn btn-primary" :disabled="notifyLoading" @click="sendNotification">
              <i class="fas fa-paper-plane me-1"></i>
              {{ notifyLoading ? 'Đang gửi...' : 'Gửi thông báo' }}
            </button>
          </div>
        </div>

        <div class="mt-3">
          <div v-if="notifyMessage" class="alert alert-success py-2 px-3 mb-2">{{ notifyMessage }}</div>
          <div v-else-if="notifyError" class="alert alert-danger py-2 px-3 mb-2">{{ notifyError }}</div>
        </div>

        <div class="recent-notices mt-2">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <h6 class="mb-0">Thông báo gần đây</h6>
            <small class="text-muted">Hiển thị 8 thông báo mới nhất</small>
          </div>
          <ul class="list-group list-group-flush">
            <li v-for="notice in adminNotices" :key="notice.id" class="list-group-item recent-notice-item">
              <div class="d-flex justify-content-between align-items-start gap-2">
                <div class="flex-grow-1">
                  <div class="fw-semibold">{{ notice.title }}</div>
                  <div class="text-muted small">{{ notice.message }}</div>
                  <div class="small text-muted mt-1">{{ formatDate(notice.createdAt) }}</div>
                </div>
                <div class="d-flex align-items-center gap-2">
                  <span class="badge bg-primary-subtle text-primary text-uppercase">{{ mapNoticeType(notice.type) }}</span>
                  <button 
                    class="btn btn-sm btn-outline-danger btn-delete-notice" 
                    @click="openDeleteModal(notice)"
                    :disabled="Boolean(deletingNoticeId)"
                    title="Xóa thông báo"
                  >
                    <i class="fas fa-trash-alt"></i>
                  </button>
                </div>
              </div>
            </li>
            <li v-if="!adminNotices.length" class="list-group-item text-muted small">
              Chưa có thông báo được gửi.
            </li>
          </ul>
        </div>
      </div>

      <div v-if="errorMessage" class="alert alert-danger mt-3">{{ errorMessage }}</div>
      <div v-if="loading" class="loading-overlay">
        <div class="spinner-border text-primary" role="status"></div>
        <span class="ms-2">Đang tải dữ liệu...</span>
      </div>
    </div>
  </div>

  <div v-if="deleteModal.visible" class="modal-backdrop fade show"></div>
  <div
    v-if="deleteModal.visible"
    class="modal d-block confirm-delete-modal"
    tabindex="-1"
    @click.self="closeDeleteModal"
    role="dialog"
    aria-modal="true"
    aria-labelledby="deleteModalTitle"
  >
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0">
        <div class="modal-header border-0">
          <div class="d-flex align-items-center gap-2">
            <div class="delete-icon">
              <i class="fas fa-trash-alt"></i>
            </div>
            <div>
              <h5 id="deleteModalTitle" class="modal-title mb-0">Xóa thông báo</h5>
              <small class="text-muted">Bạn có chắc muốn xóa thông báo này?</small>
            </div>
          </div>
          <button type="button" class="btn-close" @click="closeDeleteModal" aria-label="Đóng"></button>
        </div>
        <div class="modal-body">
          <p class="mb-1 fw-semibold">{{ deleteModal.notice?.title }}</p>
          <p class="text-muted small mb-2">{{ deleteModal.notice?.message }}</p>
          <div class="alert alert-warning d-flex align-items-start gap-2 mb-0">
            <i class="fas fa-info-circle mt-1"></i>
            <div>
              <div class="fw-semibold text-dark">Hành động không thể hoàn tác</div>
              <small>Thông báo sẽ bị xóa khỏi hệ thống và khách hàng không còn thấy nữa.</small>
            </div>
          </div>
        </div>
        <div class="modal-footer border-0">
          <button type="button" class="btn btn-light" @click="closeDeleteModal">Hủy</button>
          <button type="button" class="btn btn-danger" :disabled="Boolean(deletingNoticeId)" @click="confirmDelete">
            {{ deletingNoticeId ? 'Đang xóa...' : 'Xóa' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../../../services/api'

const summary = ref({
  ticketsToday: 0,
  revenueToday: 0,
  newCustomers: 0,
  ratingScore: 0,
  ticketsTodayDelta: 0,
  revenueTodayDelta: 0,
  ratingBase: 5,
})
const counters = ref({
  trips: 0,
  orders: 0,
  customers: 0,
  revenue: 0,
})
const monthlyRevenue = ref([])
const recentOrders = ref([])
const topRoutes = ref([])
const loading = ref(false)
const errorMessage = ref('')
const lastUpdated = ref(null)
const adminNotices = ref([])
const notifyForm = ref({
  title: '',
  message: '',
  type: 'info',
  emails: '',
})
const notifyLoading = ref(false)
const notifyError = ref('')
const notifyMessage = ref('')
const customerOptions = ref([])
const selectedCustomerIds = ref([])
const deleteModal = ref({
  visible: false,
  notice: null,
})
const deletingNoticeId = ref(null)

const formatCurrency = (value) =>
  new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value || 0)
const formatNumber = (value) => Number(value || 0).toLocaleString('vi-VN')
const formatDate = (value) => (value ? new Date(value).toLocaleString('vi-VN') : '—')
const formatMonth = (value) => {
  if (!value) return '—'
  const [year, month] = value.split('-')
  return `${month}/${year}`
}
const computeRevenueWidth = (value) => {
  const max = Math.max(...monthlyRevenue.value.map((item) => item.total), 1)
  return `${Math.round((value / max) * 100)}%`
}
const formatTrend = (value) => {
  const number = Number(value || 0)
  if (number === 0) return '—'
  return `${number > 0 ? '+' : ''}${formatNumber(number)}`
}
const trendClass = (value) => ({
  'text-success': value > 0,
  'text-danger': value < 0,
})
const mapStatus = (status) => {
  const dict = {
    cho_xu_ly: 'Chờ xử lý',
    da_xac_nhan: 'Đã xác nhận',
    hoan_tat: 'Hoàn tất',
    da_huy: 'Đã huỷ',
  }
  return dict[status] || 'Không rõ'
}
const mapNoticeType = (type) => {
  const dict = {
    info: 'Thông tin',
    success: 'Thành công',
    warning: 'Cảnh báo',
    error: 'Lỗi',
    inbox: 'Hộp thư đến',
    trip_update: 'Chuyến đi',
  }
  return dict[type] || 'Thông báo'
}

const loadDashboard = async () => {
  loading.value = true
  errorMessage.value = ''
  try {
    const { data } = await api.get('/admin/statistics/overview')
    const payload = data?.data || {}
    summary.value = {
      ticketsToday: payload.summary?.ticketsToday || 0,
      revenueToday: payload.summary?.revenueToday || 0,
      newCustomers: payload.summary?.newCustomers || 0,
      ratingScore: payload.summary?.ratingScore || 0,
      ticketsTodayDelta: payload.summary?.ticketsTodayDelta || 0,
      revenueTodayDelta: payload.summary?.revenueTodayDelta || 0,
      ratingBase: payload.summary?.ratingBase || 5,
    }
    counters.value = {
      trips: payload.counters?.trips || 0,
      orders: payload.counters?.orders || 0,
      customers: payload.counters?.customers || 0,
      revenue: payload.counters?.revenue || 0,
    }
    monthlyRevenue.value = payload.monthlyRevenue || []
    recentOrders.value = payload.recentOrders || []
    topRoutes.value = payload.topRoutes || []
    lastUpdated.value = new Date().toISOString()
  } catch (error) {
    const errorMsg = error.response?.data?.message || 'Không thể tải dữ liệu bảng điều khiển. Vui lòng thử lại.'
    errorMessage.value = errorMsg
    window.$toast?.error?.(errorMsg)
  } finally {
    loading.value = false
  }
}

const parseEmails = (value) =>
  (value || '')
    .split(/[,;\n]/)
    .map((item) => item.trim())
    .filter(Boolean)

const mapAdminNotice = (item) => ({
  id: item.id,
  title: item.title || item.tieu_de || 'Thông báo',
  message: item.message || item.noi_dung || '',
  type: item.type || item.loai || 'info',
  createdAt: item.createdAt || item.created_at || item.ngay_tao || null,
})

const loadAdminNotifications = async () => {
  notifyError.value = ''
  try {
    const { data } = await api.get('/admin/notifications', { params: { limit: 8 } })
    adminNotices.value = (data?.data || []).map(mapAdminNotice)
  } catch (error) {
    const errorMsg = error.response?.data?.message || 'Không thể tải danh sách thông báo. Vui lòng thử lại.'
    notifyError.value = errorMsg
    window.$toast?.error?.(errorMsg)
  }
}

const loadCustomerOptions = async () => {
  try {
    const { data } = await api.get('/admin/users', {
      params: { role: 'customer', status: 'active', perPage: 200 },
    })
    const list = Array.isArray(data?.data) ? data.data : []
    customerOptions.value = list
      .filter((item) => item?.email)
      .map((item) => ({
        id: item.id,
        label: `${item.ho_ten || item.name || 'Khách'} • ${item.email}`,
      }))
  } catch (error) {
    console.warn('Cannot load customer list', error)
  }
}

const sendNotification = async () => {
  notifyError.value = ''
  notifyMessage.value = ''

  if (!notifyForm.value.title.trim() || !notifyForm.value.message.trim()) {
    notifyError.value = 'Vui lòng nhập tiêu đề và nội dung thông báo.'
    window.$toast?.warning?.('Vui lòng nhập tiêu đề và nội dung thông báo.')
    return
  }

  notifyLoading.value = true
  window.$toast?.info?.('Đang gửi thông báo...')
  try {
    const payload = {
      title: notifyForm.value.title.trim(),
      message: notifyForm.value.message.trim(),
      type: notifyForm.value.type || 'info',
    }

    if (selectedCustomerIds.value.length) {
      payload.recipientIds = selectedCustomerIds.value
    }

    const emails = parseEmails(notifyForm.value.emails)
    if (emails.length) {
      payload.recipientEmails = emails
    }

    const { data } = await api.post('/admin/notifications', payload)
    const successMsg = data?.message || `Đã gửi thông báo tới ${data?.data?.recipients || 'khách hàng'}`
    notifyMessage.value = successMsg
    window.$toast?.success?.(successMsg + ' 📧')
    notifyForm.value.message = ''
    notifyForm.value.title = ''
    notifyForm.value.emails = ''
    selectedCustomerIds.value = []
    await loadAdminNotifications()
  } catch (error) {
    const errorMsg = error.response?.data?.message || 'Không gửi được thông báo. Vui lòng thử lại.'
    notifyError.value = errorMsg
    window.$toast?.error?.(errorMsg)
  } finally {
    notifyLoading.value = false
  }
}

const openDeleteModal = (notice) => {
  deleteModal.value = {
    visible: true,
    notice,
  }
}

const closeDeleteModal = () => {
  deleteModal.value = {
    visible: false,
    notice: null,
  }
}

const confirmDelete = async () => {
  const noticeId = deleteModal.value.notice?.id
  if (!noticeId || deletingNoticeId.value) return

  deletingNoticeId.value = noticeId
  try {
    await api.delete(`/admin/notifications/${noticeId}`)
    window.$toast?.success?.('Đã xóa thông báo thành công! 🗑️')
    adminNotices.value = adminNotices.value.filter((notice) => notice.id !== noticeId)
    await loadAdminNotifications()
  } catch (error) {
    const errorMsg = error.response?.data?.message || 'Không thể xóa thông báo. Vui lòng thử lại.'
    window.$toast?.error?.(errorMsg)
  } finally {
    deletingNoticeId.value = null
    closeDeleteModal()
  }
}

onMounted(() => {
  loadDashboard()
  loadAdminNotifications()
  loadCustomerOptions()
})
</script>

<style scoped>
:root {
  --dk-primary: #007bff;
  --dk-secondary: #6c757d;
  --dk-light-bg: #fdfdfe;
  --dk-gray-bg: #f9fbfd;
  --dk-text-main: #212529;
  --dk-border: #e9ecef;
}

.admin-dashboard {
  padding: 0;
  background: var(--dk-gray-bg);
  min-height: 100vh;
}

.card {
  border: 1px solid var(--dk-border);
  border-radius: 16px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03);
  background: var(--dk-light-bg);
  overflow: hidden;
}

.header-bar {
  border-bottom: 1px solid var(--dk-border);
  padding-bottom: 15px;
  margin-bottom: 25px !important;
}
.page-title {
  color: var(--dk-text-main);
  font-weight: 700;
  font-size: 1.75rem;
}
.page-title i {
  color: var(--dk-primary);
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 24px;
}
.stat-card {
  display: flex;
  align-items: center;
  background: #fff;
  border-radius: 12px;
  padding: 20px 24px;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.03);
  border: 1px solid var(--dk-border);
  transition: box-shadow 0.2s ease, transform 0.2s ease;
}
.stat-card:hover {
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
  transform: translateY(-3px);
}
.icon-box {
  width: 50px;
  height: 50px;
  border-radius: 12px;
  display: grid;
  place-items: center;
  font-size: 24px;
  color: #fff;
  flex-shrink: 0;
  margin-right: 18px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}
.stat-card.blue .icon-box {
  background: linear-gradient(135deg, #007bff, #00c6ff);
  box-shadow: 0 4px 12px rgba(0, 123, 255, 0.4);
}
.stat-card.green .icon-box {
  background: linear-gradient(135deg, #28a745, #20c997);
  box-shadow: 0 4px 12px rgba(40, 167, 69, 0.4);
}
.stat-card.purple .icon-box {
  background: linear-gradient(135deg, #6f42c1, #e83e8c);
  box-shadow: 0 4px 12px rgba(111, 66, 193, 0.4);
}
.stat-card.orange .icon-box {
  background: linear-gradient(135deg, #fd7e14, #ffc107);
  box-shadow: 0 4px 12px rgba(253, 126, 20, 0.4);
}
.info h5 {
  font-size: 0.9rem;
  font-weight: 500;
  margin-bottom: 4px;
  color: var(--dk-secondary);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}
.info p {
  font-size: 1.8rem;
  font-weight: 800;
  color: var(--dk-text-main);
  margin: 0;
}
.stat-card.green .info p {
  color: #28a745;
}

.dashboard-content {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 24px;
  margin-top: 30px;
}
.chart-box,
.activity-box {
  background: #fff;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
  border: 1px solid var(--dk-border);
  min-height: 260px;
}
.chart-box h5,
.activity-box h5 {
  color: var(--dk-text-main);
  font-weight: 600;
  font-size: 1.05rem;
  margin-bottom: 15px;
}
.chart-box h5 i,
.activity-box h5 i {
  color: var(--dk-primary);
}

.empty-box {
  border: 2px dashed #d9e2f3;
  border-radius: 10px;
  color: var(--dk-secondary);
  text-align: center;
  padding: 50px 0;
  margin-top: 12px;
  font-style: italic;
  background: var(--dk-gray-bg);
  transition: all 0.3s ease;
}
.empty-box:hover {
  border-color: #007bff;
  color: #007bff;
  background: #eef4f8;
}

.revenue-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}
.revenue-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  padding: 10px 0;
  border-bottom: 1px solid #edf0f6;
}
.revenue-row:last-child {
  border-bottom: none;
}
.revenue-label {
  display: flex;
  flex-direction: column;
  gap: 2px;
  min-width: 110px;
  font-size: 0.95rem;
}
.revenue-bar {
  flex: 1;
  background: #edf0f6;
  border-radius: 999px;
  height: 10px;
  overflow: hidden;
}
.revenue-bar-inner {
  display: block;
  height: 100%;
  background: linear-gradient(135deg, #007bff, #00c6ff);
  border-radius: 999px;
  transition: width 0.3s ease;
}

.activity-list {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 14px;
}
.activity-list li {
  padding-bottom: 12px;
  border-bottom: 1px dashed #e0e7f1;
}
.activity-list li:last-child {
  border-bottom: none;
}
.order-title {
  display: flex;
  justify-content: space-between;
  margin-bottom: 4px;
  gap: 8px;
}
.order-meta {
  display: flex;
  justify-content: space-between;
  font-size: 0.9rem;
  color: #6b7280;
}
.order-meta i {
  margin-right: 4px;
}
.status-badge {
  background: rgba(0, 123, 255, 0.12);
  color: #0053c0;
  font-size: 0.72rem;
  padding: 2px 8px;
  border-radius: 999px;
  margin-left: 8px;
}

.top-routes ul {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.top-routes li {
  display: flex;
  justify-content: space-between;
  font-size: 0.9rem;
  border-bottom: 1px dashed #e0e7f1;
  padding-bottom: 6px;
}
.top-routes li:last-child {
  border-bottom: none;
}

.notification-panel {
  position: relative;
  background: linear-gradient(180deg, #fbfdff 0%, #f6f8fb 100%);
  border: 1px solid #e5e7eb;
  border-radius: 16px;
  padding: 18px;
  box-shadow: 0 12px 30px rgba(17, 24, 39, 0.08);
}
.notification-panel .form-control,
.notification-panel .form-select {
  background: #fff;
  border-radius: 10px;
  border: 1px solid #e5e7eb;
  box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.02);
  transition: all 0.2s ease;
}
.notification-panel .form-control:focus,
.notification-panel .form-select:focus {
  border-color: #2563eb;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
}
.customer-select-wrapper {
  border: 1px dashed #d6d9e0;
  border-radius: 12px;
  padding: 12px;
  background: #fff;
}
.customer-multi {
  font-size: 0.95rem;
  min-height: 120px;
}
.notification-panel .form-label {
  color: #374151;
}
.notification-panel small.text-muted {
  color: #6b7280 !important;
}
.notification-panel .btn-primary {
  min-width: 160px;
  border-radius: 10px;
  box-shadow: 0 10px 24px rgba(37, 99, 235, 0.25);
}
.notification-panel .btn-light {
  border-radius: 10px;
}
.recent-notices .list-group-item {
  border: none;
  border-bottom: 1px dashed #e5e9f2;
}
.recent-notice-item {
  background: transparent;
}
.recent-notice-item .badge {
  border-radius: 999px;
  padding: 6px 10px;
  font-weight: 700;
  letter-spacing: 0.2px;
}
.btn-delete-notice {
  padding: 4px 8px;
  border-radius: 6px;
  transition: all 0.2s ease;
}
.btn-delete-notice:hover:not(:disabled) {
  background-color: #dc3545;
  border-color: #dc3545;
  color: #fff;
  transform: scale(1.05);
}
.btn-delete-notice:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
.recent-notice-item .text-muted {
  line-height: 1.4;
}
.recent-notice-item:last-child {
  border-bottom: none;
}

.loading-overlay {
  position: absolute;
  inset: 0;
  background: rgba(255, 255, 255, 0.75);
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 10px;
}

.confirm-delete-modal .modal-content {
  border-radius: 18px;
  box-shadow: 0 25px 60px rgba(15, 23, 42, 0.15);
}
.confirm-delete-modal .delete-icon {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  background: rgba(220, 53, 69, 0.12);
  color: #dc3545;
  display: grid;
  place-items: center;
  font-size: 18px;
}
.confirm-delete-modal .modal-body {
  padding-top: 0;
}
.confirm-delete-modal .alert {
  border-radius: 12px;
  background-color: #fff9eb;
  border: 1px solid rgba(255, 193, 7, 0.35);
}
.confirm-delete-modal .btn-light {
  border-radius: 10px;
}
.confirm-delete-modal .btn-danger {
  border-radius: 10px;
  min-width: 100px;
}

@media (max-width: 992px) {
  .dashboard-content {
    grid-template-columns: 1fr;
  }
}
</style>
