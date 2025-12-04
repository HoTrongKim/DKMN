<template>
  <div class="quan-ly-chuyen-di-page">
    <div class="page-container">
      <div class="card p-4 shadow-sm rounded-3">
        <!-- ===== HEADER ===== -->
        <div class="header-bar d-flex align-items-center justify-content-between flex-wrap mb-3">
          <h4 class="page-title m-0">
            <i class="fas fa-route me-2"></i> Quản Lý Chuyến Đi
          </h4>
          <div class="actions d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-grad-add" @click="openCreate">
              <i class="fas fa-plus-circle me-1"></i> Thêm chuyến
            </button>
            <button type="button" class="btn btn-grad-edit" :disabled="!selectedId" @click="openEditSelected">
              <i class="fas fa-edit me-1"></i> Chỉnh sửa
            </button>
            <button type="button" class="btn btn-grad-del" :disabled="!selectedId" @click="openDeleteSelected">
              <i class="fas fa-trash-alt me-1"></i> Xóa chuyến
            </button>
          </div>
        </div>

        <!-- ===== FILTER BAR ===== -->
        <div class="filters-bar d-flex flex-wrap align-items-center gap-2 mb-3">
          <div class="filter-left d-flex align-items-center gap-2" style="flex:1; min-width:0">
            <input
              class="form-control form-control-sm search-input"
              style="min-width:0; flex:1"
              v-model="filters.keyword"
              placeholder="Tìm tuyến / nhà xe / mã"
            />
            <select class="form-select form-select-sm ms-1" style="width:140px" v-model="filters.type">
              <option value="">Tất cả loại</option>
              <option value="bus">Xe khách</option>
              <option value="train">Tàu hỏa</option>
              <option value="plane">Máy bay</option>
            </select>
            <select class="form-select form-select-sm ms-1" style="width:160px" v-model="filters.status">
              <option value="">Tất cả trạng thái</option>
              <option value="AVAILABLE">Còn vé</option>
              <option value="SOLD_OUT">Hết vé</option>
              <option value="CANCELLED">Hủy</option>
              <option value="COMPLETED">Đã hoàn thành</option>
            </select>
            <input type="date" class="form-control form-control-sm ms-1" style="width:150px" v-model="filters.fromDate" />
            <input type="date" class="form-control form-control-sm ms-1" style="width:150px" v-model="filters.toDate" />
          </div>

          <div class="filter-actions d-flex align-items-center ms-auto">
            <button type="button" class="btn btn-refresh ms-2" @click="resetFilters">
              <i class="fas fa-rotate me-1"></i> Làm mới
            </button>
          </div>
        </div>

        <!-- ===== TABLE ===== -->
        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead class="table-light border-bottom">
              <tr>
                <th style="width:42px;"></th>
                <th class="sortable" @click="sortBy('type')">Loại <i :class="sortIcon('type')"></i></th>
                <th class="sortable" @click="sortBy('route')">Tuyến <i :class="sortIcon('route')"></i></th>
                <th class="sortable" @click="sortBy('departAt')">Khởi hành <i :class="sortIcon('departAt')"></i></th>
                <th class="sortable" @click="sortBy('basePrice')">Giá <i :class="sortIcon('basePrice')"></i></th>
                <th class="sortable" @click="sortBy('availableSeats')">Còn chỗ <i :class="sortIcon('availableSeats')"></i></th>
                <th class="sortable" @click="sortBy('status')">Trạng thái <i :class="sortIcon('status')"></i></th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="isLoading">
                <td colspan="7" class="text-center text-muted py-4">
                  <div class="mb-2">
                    <span class="spinner-border text-primary" role="status"></span>
                  </div>
                  Đang tải danh sách chuyến đi...
                </td>
              </tr>
              <tr v-else-if="loadError">
                <td colspan="7" class="text-center text-danger py-4">
                  <div class="mb-2"><i class="fas fa-exclamation-triangle"></i></div>
                  {{ loadError }}
                  <div class="mt-2">
                    <button class="btn btn-outline-primary btn-sm" @click="fetchTrips()">Thử lại</button>
                  </div>
                </td>
              </tr>
              <tr v-else-if="pagedItems.length === 0">
                <td colspan="7" class="text-center text-muted py-4">
                  <div class="mb-2"><i class="far fa-folder-open fa-lg"></i></div>
                  Chưa có dữ liệu hiển thị.
                </td>
              </tr>
              <tr v-else v-for="(it, idx) in pagedItems" :key="it.id || idx" :class="{'row-selected': it.id===selectedId}">
                <td>
                  <input class="form-check-input" type="radio" name="rowSelect" :value="it.id" v-model="selectedId">
                </td>
                <td>
                  <span :class="['badge', typeBadge(it.type)]">
                    <i :class="typeIcon(it.type)" class="me-1"></i>{{ typeText(it.type) }}
                  </span>
                </td>
                <td>
                  <div class="fw-semibold text-dark">{{ it.departureLocation }} → {{ it.arrivalLocation }}</div>
                  <div class="text-muted small">{{ it.carrier }}</div>
                </td>
                <td class="fw-semibold">{{ fmtDateTime(it.departAt) }}</td>
                <td>{{ fmtCurrency(it.basePrice) }}</td>
                <td>{{ it.availableSeats }}/{{ it.totalSeats }}</td>
                <td>
                  <span :class="['status-dot me-2', statusDot(it.status)]"></span>
                  <span :class="['badge', statusBadge(it.status)]">{{ statusText(it.status) }}</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- ===== PAGINATION ===== -->
                <div class="d-flex justify-content-between align-items-center">
          <small class="text-muted">Tổng: {{ pagination.total }}</small>
          <ul class="pagination mb-0">
            <li :class="['page-item', {disabled: pagination.currentPage===1 || isLoading}]">
              <button
                class="page-link"
                @click="goPrev"
                :disabled="pagination.currentPage===1 || isLoading"
              >
                &laquo;
              </button>
            </li>
            <li class="page-item disabled"><span class="page-link">{{ pagination.currentPage }}/{{ totalPages || 1 }}</span></li>
            <li :class="['page-item', {disabled: pagination.currentPage===totalPages || totalPages===0 || isLoading}]">
              <button
                class="page-link"
                @click="goNext"
                :disabled="pagination.currentPage===totalPages || totalPages===0 || isLoading"
              >
                &raquo;
              </button>
            </li>
          </ul>
        </div>

      </div>
    </div>

    <!-- ===== MODAL ADD/EDIT ===== -->
    <div v-if="showModal" class="modal-backdrop fade show"></div>
    <div v-if="showModal" class="modal d-block" tabindex="-1" @click.self="closeModal">
      <div class="modal-dialog">
        <form class="modal-content" @submit.prevent="onSubmit">
          <div class="modal-header bg-soft-warning">
            <h5 class="modal-title">{{ isEdit ? 'Chỉnh sửa chuyến' : 'Thêm chuyến mới' }}</h5>
            <button type="button" class="btn-close" @click="closeModal"></button>
          </div>
          <div class="modal-body">
            <div class="row g-3">
              <div class="col-6">
                <label class="form-label">Loại phương tiện</label>
                <select class="form-select" v-model="form.type" required>
                  <option value="bus">Xe khách</option>
                  <option value="train">Tàu hỏa</option>
                  <option value="plane">Máy bay</option>
                </select>
              </div>
              <div class="col-6">
                <label class="form-label">Nhà vận hành</label>
                <select class="form-select" v-model="form.operatorId" :disabled="filteredOperators.length === 0" required>
                  <option value="" disabled>-- Chọn nhà vận hành --</option>
                  <option v-for="op in filteredOperators" :key="op.id" :value="String(op.id)">{{ op.ten }}</option>
                </select>
                <small class="text-muted">{{ formOperatorName || 'Chưa chọn nhà vận hành' }}</small>
              </div>
              <div class="col-6">
                <label class="form-label">Nơi đi (tỉnh/thành)</label>
                <select class="form-select" v-model="form.fromProvinceId" :disabled="provinces.length === 0" required>
                  <option value="" disabled>-- Chọn tỉnh/thành --</option>
                  <option v-for="city in provinces" :key="'from-city-' + city.id" :value="String(city.id)">
                    {{ city.ten }}
                  </option>
                </select>
              </div>
              <div class="col-6">
                <label class="form-label">Nơi đến (tỉnh/thành)</label>
                <select class="form-select" v-model="form.toProvinceId" :disabled="provinces.length === 0" required>
                  <option value="" disabled>-- Chọn tỉnh/thành --</option>
                  <option v-for="city in provinces" :key="'to-city-' + city.id" :value="String(city.id)">
                    {{ city.ten }}
                  </option>
                </select>
              </div>
              <div class="col-6">
                <label class="form-label">Điểm đi</label>
                <select class="form-select" v-model="form.fromStationId" :disabled="filteredFromStations.length === 0" required>
                  <option value="" disabled>-- Chọn điểm đi --</option>
                  <option v-for="st in filteredFromStations" :key="'from-' + st.id" :value="String(st.id)">
                    {{ st.ten }} ({{ st.dia_chi }})
                  </option>
                </select>
              </div>
              <div class="col-6">
                <label class="form-label">Điểm đến</label>
                <select class="form-select" v-model="form.toStationId" :disabled="filteredToStations.length === 0" required>
                  <option value="" disabled>-- Chọn điểm đến --</option>
                  <option v-for="st in filteredToStations" :key="'to-' + st.id" :value="String(st.id)">
                    {{ st.ten }} ({{ st.dia_chi }})
                  </option>
                </select>
              </div>
              <div class="col-6">
                <label class="form-label">Giờ khởi hành</label>
                <input type="datetime-local" class="form-control" v-model="form.departureTime" required />
              </div>
              <div class="col-6">
                <label class="form-label">Giờ đến</label>
                <input type="datetime-local" class="form-control" v-model="form.arrivalTime" required />
              </div>
              <div class="col-6">
                <label class="form-label">Giá cơ bản (VND)</label>
                <input type="number" class="form-control" v-model.number="form.basePrice" min="0" required />
              </div>
              <div class="col-6">
                <label class="form-label">Tổng ghế</label>
                <input type="number" class="form-control" v-model.number="form.totalSeats" min="1" required />
              </div>
              <div class="col-6">
                <label class="form-label">Còn ghế</label>
                <input type="number" class="form-control" v-model.number="form.availableSeats" :max="form.totalSeats || 0" min="0" required />
              </div>
              <div class="col-6">
                <label class="form-label">Trạng thái</label>
                <select class="form-select" v-model="form.status">
                  <option value="AVAILABLE">Còn vé</option>
                  <option value="SOLD_OUT">Hết vé</option>
                  <option value="CANCELLED">Hủy</option>
                  <option value="COMPLETED" disabled>Đã hoàn thành (tự động)</option>
                </select>
              </div>
              <div class="col-12" v-if="formError">
                <div class="alert alert-danger py-2 px-3 mb-0">
                  <i class="fas fa-exclamation-triangle me-1"></i>{{ formError }}
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer d-flex justify-content-between">
            <button class="btn btn-grad-notify" type="button" v-if="isEdit" @click="openNotify">
              <i class="fas fa-paper-plane me-1"></i> Thông báo khách hàng
            </button>
            <div class="ms-auto">
              <button class="btn btn-ghost" type="button" @click="closeModal">Đóng</button>
              <button class="btn btn-grad-add" type="submit" :disabled="!canSubmit || submitting">
                <span v-if="submitting" class="spinner-border spinner-border-sm me-1" role="status"></span>
                {{ isEdit ? 'Lưu thay đổi' : 'Tạo chuyến' }}
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- ===== MODAL DELETE ===== -->
    <div v-if="confirming" class="modal-backdrop fade show"></div>
    <div v-if="confirming" class="modal d-block" tabindex="-1" @click.self="confirming=false">
      <div class="modal-dialog modal-sm">
        <div class="modal-content">
          <div class="modal-header bg-soft-danger">
            <h6 class="modal-title"><i class="fas fa-trash-alt me-2 text-danger"></i>Xóa chuyến</h6>
            <button type="button" class="btn-close" @click="confirming=false"></button>
          </div>
          <div class="modal-body">
            Xóa chuyến <strong>{{ selectedItem ? (selectedItem.departureLocation + ' → ' + selectedItem.arrivalLocation) : 'này' }}</strong>?
            <p class="text-danger small mb-0 mt-2" v-if="deleteError">{{ deleteError }}</p>
          </div>
          <div class="modal-footer">
            <button class="btn btn-ghost" @click="confirming=false">Hủy</button>
            <button class="btn btn-grad-del" @click="onDelete" :disabled="deleting">
              <span v-if="deleting" class="spinner-border spinner-border-sm me-1" role="status"></span>
              Xóa
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- ===== MODAL NOTIFY ===== -->
      <div v-if="notifyModal" class="modal-backdrop fade show"></div>
      <div v-if="notifyModal" class="modal d-block" tabindex="-1" @click.self="closeNotify">
        <div class="modal-dialog">
          <form class="modal-content" @submit.prevent>
            <div class="modal-header bg-soft-primary">
              <h5 class="modal-title"><i class="fas fa-bullhorn me-2"></i>Thông báo cho khách</h5>
              <button type="button" class="btn-close" @click="closeNotify"></button>
            </div>
            <div class="modal-body">
              <div v-if="notify.error" class="alert alert-danger py-2 mb-2">{{ notify.error }}</div>
              <div v-else-if="notify.success" class="alert alert-success py-2 mb-2">{{ notify.success }}</div>
              <div class="mb-2 small text-muted">
                Chuyến: <strong>{{ formDepartureName || '-' }} → {{ formArrivalName || '-' }}</strong>
                · Khởi hành: <strong>{{ fmtDateTime(form.departureTime) }}</strong>
              </div>
              <div class="row g-2">
                <div class="col-12">
                  <label class="form-label">Kênh gửi</label>
                  <div class="d-flex flex-wrap gap-3">
                    <label class="form-check">
                      <input class="form-check-input" type="checkbox" v-model="notify.channels.email" /> Email
                    </label>
                    <label class="form-check">
                      <input class="form-check-input" type="checkbox" v-model="notify.channels.app" /> Thông báo ứng dụng
                    </label>
                    <label class="form-check">
                      <input class="form-check-input" type="checkbox" v-model="notify.channels.sms" /> SMS
                    </label>
                  </div>
                </div>
                <div class="col-12">
                  <label class="form-label mt-2">Nội dung</label>
                  <textarea class="form-control" rows="4" v-model="notify.message" placeholder="Nhập nội dung..."></textarea>
                </div>
                <div class="col-12 mt-3">
                  <label class="form-label d-flex justify-content-between align-items-center">
                    <span>Khách hàng</span>
                    <small class="text-muted">{{ notify.recipients.length }} đã chọn</small>
                  </label>
                  <div class="mb-2">
                    <div v-if="isLoadingCustomers" class="text-muted small">Đang tải danh sách khách hàng...</div>
                    <div v-else-if="customersError" class="text-danger small">{{ customersError }}</div>
                  </div>
                  <select class="form-select" multiple size="6" :disabled="isLoadingCustomers || !customers.length" v-model="notify.recipients">
                    <option v-for="customer in customers" :key="'notify-customer-' + customer.id" :value="String(customer.id)">
                      {{ customer.name }} • {{ customer.email || customer.phone || 'Không có email' }}
                    </option>
                  </select>
                  <small class="text-muted">Giữ Ctrl (hoặc Command) để chọn nhiều khách hàng.</small>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button class="btn btn-ghost" type="button" @click="closeNotify">Đóng</button>
              <button class="btn btn-grad-notify" type="button" @click="sendNotify" :disabled="notify.loading">
                <span v-if="notify.loading" class="spinner-border spinner-border-sm me-1" role="status"></span>
                <i v-else class="fas fa-paper-plane me-1"></i> Gửi thông báo
              </button>
            </div>
          </form>
        </div>
      </div>

  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from "vue"
import api from '../../../services/api'

const DEFAULT_PER_PAGE = 10
const tripTypeToStationType = {
  bus: 'ben_xe',
  train: 'ga_tau',
  plane: 'san_bay',
}

const items = ref([])
const selectedId = ref(null)
const selectedItem = computed(() => items.value.find((it) => it.id === selectedId.value) || null)

const pagination = reactive({
  currentPage: 1,
  lastPage: 1,
  total: 0,
})

const isReady = ref(false)
const isLoading = ref(false)
const loadError = ref('')

const operators = ref([])
const stations = ref([])
const provinces = ref([])

const showModal = ref(false)
const isEdit = ref(false)
const formError = ref('')
const submitting = ref(false)
const deleting = ref(false)
const deleteError = ref('')
const confirming = ref(false)
const notifyModal = ref(false)
const customers = ref([])
const customersError = ref('')
const isLoadingCustomers = ref(false)

const form = reactive({
  id: undefined,
  type: 'bus',
  operatorId: '',
  fromProvinceId: '',
  fromStationId: '',
  toProvinceId: '',
  toStationId: '',
  departureTime: '',
  arrivalTime: '',
  basePrice: undefined,
  totalSeats: undefined,
  availableSeats: undefined,
  status: 'AVAILABLE',
})

const notify = reactive({
  channels: { email: true, app: true, sms: false },
  message: '',
  recipients: [],
  loading: false,
  error: '',
  success: '',
})

const filters = reactive({ keyword: '', type: '', status: '', fromDate: '', toDate: '' })
const sort = reactive({ key: 'departAt', dir: 'asc' })

const filteredItems = computed(() => {
  const kw = (filters.keyword || '').toLowerCase().trim()
  const from = filters.fromDate ? new Date(`${filters.fromDate}T00:00:00`) : null
  const to = filters.toDate ? new Date(`${filters.toDate}T23:59:59`) : null

  return items.value.filter((it) => {
    if (filters.type && it.type !== filters.type) return false
    if (filters.status && it.status !== filters.status) return false
    if (from && new Date(it.departAt) < from) return false
    if (to && new Date(it.departAt) > to) return false
    if (kw) {
      const haystack = `${it.departureLocation} ${it.arrivalLocation} ${it.carrier}`.toLowerCase()
      if (!haystack.includes(kw)) return false
    }
    return true
  })
})

const sortedItems = computed(() => {
  const arr = [...filteredItems.value]
  const key = sort.key
  const dir = sort.dir === 'asc' ? 1 : -1

  arr.sort((a, b) => {
    let va = key === 'route' ? `${a.departureLocation}-${a.arrivalLocation}` : a[key]
    let vb = key === 'route' ? `${b.departureLocation}-${b.arrivalLocation}` : b[key]

    if (key === 'departAt') {
      va = new Date(va).getTime()
      vb = new Date(vb).getTime()
    }

    if (typeof va === 'string') va = va.toLowerCase()
    if (typeof vb === 'string') vb = vb.toLowerCase()

    if (va < vb) return -1 * dir
    if (va > vb) return 1 * dir
    return 0
  })

  return arr
})

const pagedItems = computed(() => sortedItems.value)
const totalPages = computed(() => pagination.lastPage || 1)

const filteredOperators = computed(() => {
  if (!form.type) return operators.value
  return operators.value.filter((op) => mapOperatorType(op.loai) === form.type)
})

const filteredFromStations = computed(() => filterStationsByProvince('from'))
const filteredToStations = computed(() => filterStationsByProvince('to'))

const stationDictionary = computed(() => {
  const dict = {}
  stations.value.forEach((station) => {
    dict[String(station.id)] = station
  })
  return dict
})

const operatorDictionary = computed(() => {
  const dict = {}
  operators.value.forEach((operator) => {
    dict[String(operator.id)] = operator
  })
  return dict
})

const formDepartureName = computed(() => stationName(form.fromStationId))
const formArrivalName = computed(() => stationName(form.toStationId))
const formOperatorName = computed(() => operatorName(form.operatorId))

const canSubmit = computed(() => {
  const basePrice = Number(form.basePrice)
  const totalSeats = Number(form.totalSeats)
  const availableSeats = Number(form.availableSeats)

  return (
    !!form.operatorId &&
    !!form.fromProvinceId &&
    !!form.fromStationId &&
    !!form.toProvinceId &&
    !!form.toStationId &&
    !!form.departureTime &&
    !!form.arrivalTime &&
    !Number.isNaN(basePrice) &&
    basePrice >= 0 &&
    !Number.isNaN(totalSeats) &&
    totalSeats > 0 &&
    !Number.isNaN(availableSeats) &&
    availableSeats >= 0 &&
    availableSeats <= totalSeats
  )
})

function stationName(id) {
  return stationDictionary.value[String(id)]?.ten || ''
}

function operatorName(id) {
  return operatorDictionary.value[String(id)]?.ten || ''
}

function sortBy(key) {
  if (sort.key === key) {
    sort.dir = sort.dir === 'asc' ? 'desc' : 'asc'
  } else {
    sort.key = key
    sort.dir = 'asc'
  }
}

function sortIcon(key) {
  if (sort.key !== key) return 'fas fa-sort text-muted'
  return sort.dir === 'asc' ? 'fas fa-sort-up' : 'fas fa-sort-down'
}

function resetFilters() {
  Object.assign(filters, { keyword: '', type: '', status: '', fromDate: '', toDate: '' })
  if (isReady.value) {
    fetchTrips(1)
  }
}

function openCreate() {
  isEdit.value = false
  Object.assign(form, {
    id: undefined,
    type: filters.type || 'bus',
    operatorId: '',
    fromProvinceId: '',
    fromStationId: '',
    toProvinceId: '',
    toStationId: '',
    departureTime: '',
    arrivalTime: '',
    basePrice: undefined,
    totalSeats: undefined,
    availableSeats: undefined,
    status: 'AVAILABLE',
  })
  ensureOperatorForType(true)
  ensureStationFor('from', true)
  ensureStationFor('to', true)
  formError.value = ''
  showModal.value = true
}

function openEditSelected() {
  if (!selectedItem.value) return
  openEdit(selectedItem.value)
}

function openDeleteSelected() {
  if (!selectedItem.value) return
  deleteError.value = ''
  confirming.value = true
}

function openEdit(item) {
  if (!item) return
  isEdit.value = true
  Object.assign(form, {
    id: item.id,
    type: item.type || 'bus',
    operatorId: item.operatorId ? String(item.operatorId) : '',
    fromProvinceId: item.fromProvinceId ? String(item.fromProvinceId) : '',
    fromStationId: item.fromStationId ? String(item.fromStationId) : '',
    toProvinceId: item.toProvinceId ? String(item.toProvinceId) : '',
    toStationId: item.toStationId ? String(item.toStationId) : '',
    departureTime: toInputDate(item.departAt),
    arrivalTime: toInputDate(item.arrivalAt),
    basePrice: item.basePrice ?? undefined,
    totalSeats: item.totalSeats ?? undefined,
    availableSeats: item.availableSeats ?? undefined,
    status: item.status || 'AVAILABLE',
  })
  ensureOperatorForType()
  ensureStationFor('from', true)
  ensureStationFor('to', true)
  formError.value = ''
  showModal.value = true
}

function closeModal() {
  showModal.value = false
}

async function openNotify() {
  notify.error = ''
  notify.success = ''
  notify.message = ''
  notify.recipients = []
  notify.channels.email = true
  notify.channels.app = true
  notify.channels.sms = false
  notifyModal.value = true
  await fetchCustomers()
}

function closeNotify() {
  notifyModal.value = false
}

function goPrev() {
  if (pagination.currentPage <= 1 || isLoading.value) return
  fetchTrips(pagination.currentPage - 1)
}

function goNext() {
  if (pagination.currentPage >= totalPages.value || isLoading.value) return
  fetchTrips(pagination.currentPage + 1)
}

function validateForm() {
  if (!canSubmit.value) {
    return 'Dữ liệu chưa hợp lệ hoặc chưa đủ.'
  }

  const departure = new Date(form.departureTime)
  const arrival = new Date(form.arrivalTime)
  if (!departure || !arrival || arrival <= departure) {
    return 'Giờ đến phải sau giờ khởi hành.'
  }

  return ''
}


    /**
     * Xử lý tạo mới hoặc cập nhật chuyến đi.
     * 
     * Logic hoạt động:
     * 1. Validate form client-side (giờ đi < giờ đến, giá > 0, ...).
     * 2. Chuẩn bị payload:
     *    - Convert datetime sang ISO string (UTC/Server time).
     *    - Convert các field ID sang Number.
     * 3. Gọi API:
     *    - Nếu là Edit (`isEdit` = true): `PUT /admin/trips/{id}`.
     *    - Nếu là Create: `POST /admin/trips`.
     *    - Backend: `TripController::store` hoặc `TripController::update`.
     * 4. Xử lý kết quả:
     *    - Thành công: Đóng modal, reload danh sách, toast success.
     *    - Thất bại: Hiển thị lỗi từ response.
     */
    async function onSubmit() {
      formError.value = ''
      const err = validateForm()
      if (err) {
        formError.value = err
        window.$toast?.warning?.(err)
        return
      }

      const payload = {
        operatorId: Number(form.operatorId),
        fromProvinceId: Number(form.fromProvinceId),
        fromStationId: Number(form.fromStationId),
        toProvinceId: Number(form.toProvinceId),
        toStationId: Number(form.toStationId),
        departureTime: toServerDate(form.departureTime),
        arrivalTime: toServerDate(form.arrivalTime),
        basePrice: Number(form.basePrice),
        totalSeats: Number(form.totalSeats),
        remainingSeats: Number(form.availableSeats),
        status: form.status,
      }

      submitting.value = true
      try {
        if (isEdit.value && form.id) {
          await api.put(`/admin/trips/${form.id}`, payload)
          window.$toast?.success?.('Cập nhật chuyến đi thành công! ✅')
        } else {
          await api.post('/admin/trips', payload)
          window.$toast?.success?.('Tạo chuyến đi thành công! 🚌')
        }
        closeModal()
        await fetchTrips(isEdit.value ? pagination.currentPage : 1)
      } catch (error) {
        const errorMsg = resolveError(error, 'Không thể lưu chuyến đi.')
        formError.value = errorMsg
        window.$toast?.error?.(errorMsg)
      } finally {
        submitting.value = false
      }
    }

    /**
     * Xóa chuyến đi đã chọn.
     * 
     * Logic hoạt động:
     * 1. Kiểm tra `selectedId` có hợp lệ không.
     * 2. Gọi API `DELETE /admin/trips/{id}`.
     *    - Backend: `TripController::destroy`.
     *    - Backend sẽ kiểm tra ràng buộc (ví dụ: chuyến đã có vé đặt thì không cho xóa hoặc xóa mềm).
     * 3. Xử lý kết quả:
     *    - Thành công: Reset selection, reload danh sách (xử lý lùi trang nếu xóa hết item trang cuối).
     *    - Thất bại: Toast error.
     */
    async function onDelete() {
      if (!selectedId.value) {
        confirming.value = false
        return
      }
      deleteError.value = ''
      if (deleting.value) return
      deleting.value = true

      const currentCount = items.value.length
      const nextPage = pagination.currentPage > 1 && currentCount === 1 ? pagination.currentPage - 1 : pagination.currentPage

      try {
        await api.delete(`/admin/trips/${selectedId.value}`)
        confirming.value = false
        selectedId.value = null
        window.$toast?.success?.('Đã xóa chuyến đi thành công! 🗑️')
        await fetchTrips(nextPage)
      } catch (error) {
        const errorMsg = resolveError(error, 'Không thể xóa chuyến đi.')
        deleteError.value = errorMsg
        window.$toast?.error?.(errorMsg)
      } finally {
        deleting.value = false
      }
    }

    /**
     * Các hàm tải dữ liệu danh mục (Lookup Data) từ Backend.
     * 
     * - fetchOperators: GET /dkmn/nha-van-hanh/get-data (Lấy danh sách nhà xe/hãng bay)
     * - fetchStations: GET /dkmn/tram/get-data (Lấy danh sách bến xe/sân bay/ga)
     * - fetchCustomers: GET /admin/users?role=customer (Lấy danh sách khách hàng để gửi thông báo)
     * - fetchProvinces: GET /dkmn/tinh-thanh/get-data (Lấy danh sách tỉnh thành)
     */
    async function fetchOperators() {
      try {
        const { data } = await api.get('/dkmn/nha-van-hanh/get-data')
        operators.value = Array.isArray(data?.data) ? data.data : []
      } catch (error) {
        operators.value = []
        console.warn('Cannot load operators', error)
      } finally {
        ensureOperatorForType()
      }
    }

    async function fetchStations() {
      try {
        const { data } = await api.get('/dkmn/tram/get-data')
        stations.value = Array.isArray(data?.data) ? data.data : []
      } catch (error) {
        stations.value = []
        console.warn('Cannot load stations', error)
      } finally {
        ensureStationFor('from')
        ensureStationFor('to')
      }
    }

    async function fetchCustomers() {
      isLoadingCustomers.value = true
      customersError.value = ''
      try {
        const { data } = await api.get('/admin/users', {
          params: { role: 'customer', perPage: 200 },
        })
        customers.value = Array.isArray(data?.data) ? data.data : []
      } catch (error) {
        customers.value = []
        customersError.value = resolveError(error, 'Không tải được danh sách khách hàng.')
      } finally {
        isLoadingCustomers.value = false
      }
    }

    async function fetchProvinces() {
      try {
        const { data } = await api.get('/dkmn/tinh-thanh/get-data')
        provinces.value = (Array.isArray(data?.data) ? data.data : [])
          .map((item) => ({
            id: item.id ?? item.ma ?? item.code,
            ten: item.ten ?? item.ten_tinh ?? item.name,
          }))
          .filter((item) => item.id && item.ten)
      } catch (error) {
        provinces.value = []
        console.warn('Cannot load provinces', error)
      }
    }

    /**
     * Tải danh sách chuyến đi từ Backend với phân trang và bộ lọc.
     * 
     * Logic hoạt động:
     * 1. Chuẩn bị params từ `filters` (keyword, type, status, dateFrom, dateTo).
     * 2. Gọi API `GET /admin/trips`.
     *    - Backend: `TripController::index`.
     *    - Trả về: Danh sách chuyến đi (data) và thông tin phân trang (meta).
     * 3. Map dữ liệu trả về qua `mapTripResponse` để chuẩn hóa field cho Frontend.
     * 4. Cập nhật state `items` và `pagination`.
     */
    async function fetchTrips(page = 1) {
      isLoading.value = true
      loadError.value = ''
      try {
        const params = {
          page,
          perPage: DEFAULT_PER_PAGE,
          keyword: filters.keyword || undefined,
          status: filters.status || undefined,
          type: filters.type || undefined,
          dateFrom: filters.fromDate || undefined,
          dateTo: filters.toDate || undefined,
        }
        const { data } = await api.get('/admin/trips', { params })
        const list = Array.isArray(data?.data) ? data.data.map(mapTripResponse) : []

        items.value = list
        pagination.currentPage = data?.meta?.currentPage ?? page
        pagination.lastPage = data?.meta?.lastPage ?? 1
        pagination.total = data?.meta?.total ?? list.length

        if (!list.some((it) => it.id === selectedId.value)) {
          selectedId.value = null
        }
      } catch (error) {
        loadError.value = resolveError(error, 'Không tải được danh sách chuyến đi.')
        items.value = []
      } finally {
        isLoading.value = false
        if (!isReady.value) {
          isReady.value = true
        }
      }
    }

  function mapTripResponse(trip) {
    const totalSeats = trip.totalSeats ?? trip.seats?.total ?? 0
    const remainingSeats = trip.availableSeats ?? trip.seats?.remaining ?? 0
    const departureLocation = trip.departureLocation || trip.fromStation || ''
    const arrivalLocation = trip.arrivalLocation || trip.toStation || ''
    const type = trip.type || mapOperatorType(trip.operatorType)
    const status = trip.status || trip.rawStatus || 'AVAILABLE'

    return {
      id: trip.id,
      type: type || 'bus',
      route: trip.route || `${departureLocation} - ${arrivalLocation}`.trim(),
    departureLocation,
    arrivalLocation,
    carrier: trip.carrier || trip.operator || '',
      departAt: trip.departAt || trip.departureTime || '',
      arrivalAt: trip.arrivalAt || trip.arrivalTime || '',
      basePrice: Number(trip.basePrice ?? 0),
      totalSeats,
      availableSeats: remainingSeats,
      status,
      operatorId: trip.operatorId ?? null,
      fromStationId: trip.fromStationId ?? null,
      toStationId: trip.toStationId ?? null,
      fromProvinceId: trip.fromProvinceId ?? null,
      toProvinceId: trip.toProvinceId ?? null,
  }
}

function filterStationsByProvince(position) {
  const typeKey = tripTypeToStationType[form.type] || null
  const provinceId = position === 'from' ? form.fromProvinceId : form.toProvinceId
  const provinceValue = provinceId ? String(provinceId) : ''

  return stations.value.filter((st) => {
    const matchType = !typeKey || st.loai === typeKey
    const matchProvince = !provinceValue || String(st.tinh_thanh_id) === provinceValue
    return matchType && matchProvince
  })
}

function ensureOperatorForType(force = false) {
  const list = filteredOperators.value
  if (!list.length) {
    form.operatorId = ''
    return
  }
  const exists = list.some((op) => String(op.id) === String(form.operatorId))
  if (!exists || force) {
    form.operatorId = String(list[0].id)
  }
}

function ensureStationFor(position, force = false) {
  const list = position === 'from' ? filteredFromStations.value : filteredToStations.value
  const field = position === 'from' ? 'fromStationId' : 'toStationId'

  if (!list.length) {
    form[field] = ''
    return
  }

  const exists = list.some((st) => String(st.id) === String(form[field]))
  if (!exists || force) {
    const fallback =
      position === 'to' && list.length > 1
        ? list[1]
        : list[0]
    form[field] = String(fallback.id)
  }
}

function fmtCurrency(value) {
  if (value === undefined || value === null || Number.isNaN(Number(value))) return '--'
  return Number(value).toLocaleString('vi-VN') + ' VND'
}

function fmtDateTime(value) {
  if (!value) return '--'
  const date = new Date(value)
  if (Number.isNaN(date)) return '--'
  return date.toLocaleString('vi-VN', {
    hour12: false,
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

function typeText(type) {
  if (type === 'train') return 'Tàu hỏa'
  if (type === 'plane') return 'Máy bay'
  return 'Xe khách'
}

function typeIcon(type) {
  if (type === 'train') return 'fas fa-train'
  if (type === 'plane') return 'fas fa-plane'
  return 'fas fa-bus'
}

function typeBadge(type) {
  if (type === 'train') return 'badge-train'
  if (type === 'plane') return 'badge-plane'
  return 'badge-bus'
}

  function statusText(status) {
    if (status === 'SOLD_OUT' || status === 'HET_VE') return 'Hết vé'
    if (status === 'COMPLETED') return 'Đã hoàn thành'
    if (status === 'CANCELLED' || status === 'HUY') return 'Đã hủy'
    return 'Còn vé'
  }

  function statusBadge(status) {
    if (status === 'SOLD_OUT' || status === 'HET_VE') return 'badge-soldout'
    if (status === 'COMPLETED') return 'badge-completed'
    if (status === 'CANCELLED' || status === 'HUY') return 'badge-cancel'
    return 'badge-available'
  }

  function statusDot(status) {
    if (status === 'SOLD_OUT' || status === 'HET_VE') return 'dot-gray'
    if (status === 'COMPLETED') return 'dot-gray'
    if (status === 'CANCELLED' || status === 'HUY') return 'dot-red'
    return 'dot-green'
  }

function mapOperatorType(type) {
  if (type === 'tau_hoa') return 'train'
  if (type === 'may_bay') return 'plane'
  return 'bus'
}

function resolveError(error, fallback) {
  return error?.response?.data?.message || error?.message || fallback
}

    /**
     * Gửi thông báo cho khách hàng đã đặt vé hoặc khách hàng tiềm năng.
     * 
     * Logic hoạt động:
     * 1. Validate input: Phải chọn kênh gửi (Email, App, SMS), nội dung, và danh sách người nhận.
     * 2. Gọi API `POST /admin/trips/{id}/notify`.
     *    - Backend: `TripController::notifyCustomers`.
     *    - Backend sẽ queue job gửi email/SMS/push notification tới danh sách userIds được gửi lên.
     * 3. Hiển thị kết quả thành công/thất bại.
     */
    async function sendNotify() {
      notify.error = ''
      notify.success = ''

      if (!form.id) {
        notify.error = 'Vui lòng lưu chuyến trước khi gửi thông báo.'
        window.$toast?.warning?.('Vui lòng lưu chuyến trước khi gửi thông báo.')
        return
      }

      const selectedChannels = Object.entries(notify.channels)
        .filter(([, value]) => value)
        .map(([key]) => key)

      if (!selectedChannels.length) {
        notify.error = 'Chọn ít nhất một kênh gửi.'
        window.$toast?.warning?.('Chọn ít nhất một kênh gửi.')
        return
      }

      if (!notify.message.trim()) {
        notify.error = 'Nội dung thông báo không được để trống.'
        window.$toast?.warning?.('Nội dung thông báo không được để trống.')
        return
      }

      if (!notify.recipients.length) {
        notify.error = 'Chọn ít nhất một khách hàng.'
        window.$toast?.warning?.('Chọn ít nhất một khách hàng.')
        return
      }

      notify.loading = true
      window.$toast?.info?.('Đang gửi thông báo...')
      try {
        await api.post(`/admin/trips/${form.id}/notify`, {
          message: notify.message.trim(),
          channels: selectedChannels,
          recipientIds: notify.recipients.map((id) => Number(id)),
        })
        notify.success = 'Đã gửi thông báo.'
        window.$toast?.success?.('Đã gửi thông báo thành công! 📧')
      } catch (error) {
        const errorMsg = resolveError(error, 'Không thể gửi thông báo.')
        notify.error = errorMsg
        window.$toast?.error?.(errorMsg)
      } finally {
        notify.loading = false
      }
    }

function toInputDate(value) {
  if (!value) return ''
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return ''
  const local = new Date(date.getTime() - date.getTimezoneOffset() * 60000)
  return local.toISOString().slice(0, 16)
}

function toServerDate(value) {
  if (!value) return ''
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return ''
  return date.toISOString()
}

let keywordDebounce
watch(
  () => filters.keyword,
  () => {
    if (!isReady.value) return
    clearTimeout(keywordDebounce)
    keywordDebounce = setTimeout(() => fetchTrips(1), 400)
  }
)

watch(
  () => [filters.type, filters.status, filters.fromDate, filters.toDate],
  () => {
    if (!isReady.value) return
    fetchTrips(1)
  }
)

watch(
  () => form.type,
  () => {
    ensureOperatorForType(true)
    ensureStationFor('from', true)
    ensureStationFor('to', true)
  }
)

watch(
  () => form.fromProvinceId,
  () => {
    ensureStationFor('from', true)
  }
)

watch(
  () => form.toProvinceId,
  () => {
    ensureStationFor('to', true)
  }
)

onMounted(async () => {
  await Promise.all([fetchOperators(), fetchStations(), fetchProvinces()])
  await fetchTrips(1)
})
</script>

<style scoped>
.filters-bar .search-input{ min-width:0; }
.filters-bar .filter-left .form-select, .filters-bar .filter-left input[type="date"]{ min-width:0 }
.filters-bar .filter-actions{ margin-left:8px }
</style>

<style scoped>
.page-container{ padding-top:20px; background:#f8fafc; }

/* HEADER */
.header-bar{ border-bottom:2px solid #eef2f6; padding-bottom:10px; }
.page-title{ display:flex; align-items:center; gap:10px; font-size:1.6rem; font-weight:800; color:#0f172a; margin:0; }

/* Buttons */
.actions .btn{ border-radius:10px; font-weight:700; border:0; color:#fff; box-shadow:0 2px 6px rgba(0,0,0,.08); transition:all .2s ease; }
.btn-grad-add{  background:linear-gradient(135deg,#2f80ed,#56ccf2); }
.btn-grad-edit{ background:linear-gradient(135deg,#f78b2c,#f9d45b); color:#4a3505; }
.btn-grad-del{  background:linear-gradient(135deg,#ed2e2e,#fc835e); }
.btn-grad-notify{ background:linear-gradient(135deg,#56ccf2,#2f80ed); color:#fff; border:0; }
.actions .btn:hover{ transform:translateY(-1px); filter:brightness(1.1); }

/* Filters bar */
.filters-bar{ --ctl-h:38px; gap:8px; justify-content:flex-start; }
.filters-bar .form-control-sm,
.filters-bar .form-select-sm{ height:var(--ctl-h); flex:0 0 auto; }
.filters-bar .form-control.w-100.w-md-auto{ min-width:260px; max-width:360px; flex:0 0 auto; }
.filters-bar .form-control.w-100.w-md-auto{ width:320px; }
.filters-bar input[type="date"].form-control-sm{ height:var(--ctl-h); line-height:var(--ctl-h); }
.filters-bar .form-select-sm{ padding-right:2rem; }
.btn-refresh{
  background:linear-gradient(135deg,#4facfe,#00f2fe);
  color:#fff; border:0; border-radius:10px;
  font-weight:600; padding:0 16px; height:var(--ctl-h);
  display:inline-flex; align-items:center;
  box-shadow:0 2px 5px rgba(79,172,254,.3);
  transition:all .2s ease;
}
.btn-refresh.ms-2{ margin-left:6px !important; }
.btn-refresh:hover{ filter:brightness(1.1); transform:translateY(-1px); }
.btn-refresh:active{ transform:scale(0.98); }

/* Table */
.table-hover tbody tr:hover{ background:#f1f5f9; }
.table th{ font-weight:700; color:#111; }
.row-selected{ background:#f1f5f9; }

/* Type badges */
.badge{ border-radius:999px; padding:.45rem .6rem; font-weight:700; letter-spacing:.2px; }
.badge-bus{ background:#eaf6ff; color:#0a6ebd; }
.badge-train{ background:#e8fbf6; color:#0b9d77; }
.badge-plane{ background:#fff4e5; color:#b4690e; }

/* Status */
.status-dot{ display:inline-block; width:8px; height:8px; border-radius:50%; vertical-align:middle; }
.dot-green{ background:#3add76; } .dot-gray{ background:#9ca3af; } .dot-red{ background:#ef4444; }
.badge-available{ background:#e8fbf2; color:#137b52; }
.badge-completed{ background:#eef2f7; color:#475569; }
.badge-soldout{ background:#eef2f7; color:#475569; }
.badge-cancel{ background:#fdebec; color:#b4232e; }

/* Pagination */
.page-link{ border-radius:8px!important; color:#0f172a; }

/* Modal */
.modal-content{ border-radius:12px; overflow:hidden; }
.bg-soft-primary{ background:#eef4ff; }
.bg-soft-warning{ background:#fff7e6; }
.bg-soft-danger{ background:#fdebec; }
.btn-ghost{ background:#f3f4f6; color:#111827; border:1px solid #e5e7eb; border-radius:10px; }
.btn-ghost:hover{ background:#e5e7eb; }

/* Utility */
@media (min-width:768px){ .w-md-auto{ width:auto!important; } }
</style>







