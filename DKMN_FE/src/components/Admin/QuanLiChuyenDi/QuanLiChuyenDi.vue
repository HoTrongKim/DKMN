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
              <i class="fas fa-edit me-1"></i> Sửa chuyến
            </button>
            <button type="button" class="btn btn-grad-del" :disabled="!selectedId" @click="openDeleteSelected">
              <i class="fas fa-trash-alt me-1"></i> Xóa chuyến
            </button>
          </div>
        </div>

        <!-- ===== FILTER BAR ===== -->
  <div class="filters-bar d-flex flex-wrap align-items-center gap-2 mb-3">
          <div class="filter-left d-flex align-items-center gap-2" style="flex:1; min-width:0">
            <input class="form-control form-control-sm search-input" style="min-width:0; flex:1"
                   v-model="filters.keyword" placeholder="Tìm theo tuyến / hãng / mã" />
            <select class="form-select form-select-sm ms-1" style="width:140px" v-model="filters.type">
            <option value="">Tất cả loại</option>
            <option value="bus">Xe khách</option>
            <option value="train">Tàu</option>
            <option value="plane">Máy bay</option>
          </select>
            <select class="form-select form-select-sm ms-1" style="width:160px" v-model="filters.status">
            <option value="">Tất cả trạng thái</option>
            <option value="AVAILABLE">Còn vé</option>
            <option value="SOLD_OUT">Hết vé</option>
            <option value="CANCELLED">Hủy chuyến</option>
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
                <th class="sortable" @click="sortBy('basePrice')">Giá (từ) <i :class="sortIcon('basePrice')"></i></th>
                <th class="sortable" @click="sortBy('availableSeats')">Còn chỗ <i :class="sortIcon('availableSeats')"></i></th>
                <th class="sortable" @click="sortBy('status')">Trạng thái <i :class="sortIcon('status')"></i></th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="pagedItems.length === 0">
                <td colspan="7" class="text-center text-muted py-4">
                  <div class="mb-2"><i class="far fa-folder-open fa-lg"></i></div>
                  Chưa có dữ liệu hiển thị
                </td>
              </tr>
              <tr v-for="(it, idx) in pagedItems" :key="it.id || idx" :class="{'row-selected': it.id===selectedId}">
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
          <small class="text-muted">Tổng: {{ filteredItems.length }}</small>
          <ul class="pagination mb-0">
            <li :class="['page-item', {disabled: page===1}]">
              <button class="page-link" @click="goPrev">«</button>
            </li>
            <li class="page-item disabled"><span class="page-link">{{ page }}/{{ totalPages || 1 }}</span></li>
            <li :class="['page-item', {disabled: page===totalPages || totalPages===0}]">
              <button class="page-link" @click="goNext">»</button>
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
                  <option value="train">Tàu</option>
                  <option value="plane">Máy bay</option>
                </select>
              </div>
              <div class="col-6">
                <label class="form-label">Hãng/nhà xe</label>
                <input class="form-control" v-model.trim="form.carrier" placeholder="VD: ABC Express" required />
              </div>

              <div class="col-6">
                <label class="form-label">Nơi đi</label>
                <select class="form-select" v-model="form.departureLocation" required>
                  <option disabled value="">-- Chọn tỉnh/thành --</option>
                  <option v-for="p in provinces" :key="'from-'+p" :value="p">{{ p }}</option>
                </select>
              </div>
              <div class="col-6">
                <label class="form-label">Nơi đến</label>
                <select class="form-select" v-model="form.arrivalLocation" required>
                  <option disabled value="">-- Chọn tỉnh/thành --</option>
                  <option v-for="p in provinces" :key="'to-'+p" :value="p">{{ p }}</option>
                </select>
              </div>

              <div class="col-6">
                <label class="form-label">Khởi hành</label>
                <input type="datetime-local" class="form-control" v-model="form.departAt" required />
              </div>
              <div class="col-6">
                <label class="form-label">Giá cơ bản (VND)</label>
                <input type="number" class="form-control" v-model.number="form.basePrice" min="0" required />
              </div>
              <div class="col-6">
                <label class="form-label">Tổng ghế</label>
                <input type="number" class="form-control" v-model.number="form.totalSeats" min="0" required />
              </div>
              <div class="col-6">
                <label class="form-label">Còn ghế</label>
                <input type="number" class="form-control" v-model.number="form.availableSeats" :max="form.totalSeats || 0" min="0" required />
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
              <i class="fas fa-paper-plane me-1"></i> Cập nhật cho khách hàng
            </button>
            <div class="ms-auto">
              <button class="btn btn-ghost" type="button" @click="closeModal">Đóng</button>
              <button
                class="btn btn-grad-add"
                type="submit"
                :disabled="!canSubmit"
              >
                {{ isEdit ? 'Lưu thay đổi' : 'Thêm chuyến' }}
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
            Xóa chuyến <strong>{{ selectedItem?.departureLocation }} → {{ selectedItem?.arrivalLocation }}</strong>?
          </div>
          <div class="modal-footer">
            <button class="btn btn-ghost" @click="confirming=false">Hủy</button>
            <button class="btn btn-grad-del" @click="onDelete">Xóa</button>
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
            <h5 class="modal-title"><i class="fas fa-bullhorn me-2"></i>Cập nhật cho khách hàng</h5>
            <button type="button" class="btn-close" @click="closeNotify"></button>
          </div>
          <div class="modal-body">
            <div class="mb-2 small text-muted">
              Chuyến: <strong>{{ form.departureLocation || '—' }} → {{ form.arrivalLocation || '—' }}</strong>
              • Khởi hành: <strong>{{ fmtDateTime(form.departAt) || '—' }}</strong>
            </div>
            <div class="row g-2">
              <div class="col-12">
                <label class="form-label">Kênh gửi</label>
                <div class="d-flex flex-wrap gap-3">
                  <label class="form-check">
                    <input class="form-check-input" type="checkbox" v-model="notify.channels.email"> Email
                  </label>
                  <label class="form-check">
                    <input class="form-check-input" type="checkbox" v-model="notify.channels.app"> Thông báo hệ thống
                  </label>
                  <label class="form-check">
                    <input class="form-check-input" type="checkbox" v-model="notify.channels.sms"> SMS
                  </label>
                </div>
              </div>
              <div class="col-12">
                <label class="form-label mt-2">Nội dung gửi</label>
                <textarea class="form-control" rows="4" v-model="notify.message" placeholder="Nhập nội dung..."></textarea>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-ghost" type="button" @click="closeNotify">Đóng</button>
            <button class="btn btn-grad-notify" type="button" @click="closeNotify">
              <i class="fas fa-paper-plane me-1"></i> Gửi (UI)
            </button>
          </div>
        </form>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, reactive, computed } from "vue";

/** ====== Feature flag: bật khi có BE ====== */
const API_ENABLED = false; // <— đổi true khi có API thật

/** ====== Service stub (thay bằng API thật sau) ====== */
async function createTrip(payload){ /* await fetch(...) */ return { ok:true }; }
async function updateTrip(id,payload){ return { ok:true }; }
async function deleteTrip(id){ return { ok:true }; }

/** ====== Provinces (34) ====== */
const provinces = [
  'Hà Nội','Hải Phòng','Quảng Ninh','Bắc Ninh','Hưng Yên','Hà Nam',
  'Nam Định','Ninh Bình','Thanh Hóa','Nghệ An','Hà Tĩnh','Quảng Bình',
  'Quảng Trị','Thừa Thiên Huế','Đà Nẵng','Quảng Nam','Quảng Ngãi',
  'Bình Định','Phú Yên','Khánh Hòa','Ninh Thuận','Bình Thuận',
  'Kon Tum','Gia Lai','Đắk Lắk','Đắk Nông','Lâm Đồng',
  'Bình Phước','Bình Dương','Đồng Nai','Bà Rịa - Vũng Tàu',
  'TP. Hồ Chí Minh','Long An','Cần Thơ'
];

/** ====== State ====== */
const items = ref([]);                 // bind từ API khi có BE
const selectedId = ref(null);
const selectedItem = computed(()=> items.value.find(i=> i.id===selectedId.value));

const showModal = ref(false);
const isEdit = ref(false);
const formError = ref("");
const confirming = ref(false);
const notifyModal = ref(false);

const form = reactive({
  id: undefined,
  type: "bus",
  carrier: "",
  departureLocation: "",
  arrivalLocation: "",
  departAt: "",
  basePrice: undefined,
  totalSeats: undefined,
  availableSeats: undefined,
  status: "AVAILABLE",
});

const notify = reactive({
  channels: { email: true, app: true, sms: false },
  message: "",
});

/** ====== Filters & Table ====== */
const filters = reactive({ keyword:"", type:"", status:"", fromDate:"", toDate:"" });
const sort = reactive({ key: "departAt", dir: "asc" });
const page = ref(1);
const pageSize = ref(10);

const filteredItems = computed(() => {
  const kw = (filters.keyword||"").toLowerCase().trim();
  const from = filters.fromDate ? new Date(filters.fromDate+"T00:00:00") : null;
  const to   = filters.toDate   ? new Date(filters.toDate+"T23:59:59") : null;
  return items.value.filter(it=>{
    if (filters.type && it.type !== filters.type) return false;
    if (filters.status && it.status !== filters.status) return false;
    if (from && new Date(it.departAt) < from) return false;
    if (to   && new Date(it.departAt) > to)   return false;
    if (kw) {
      const hay = `${it.departureLocation} ${it.arrivalLocation} ${it.carrier}`.toLowerCase();
      if (!hay.includes(kw)) return false;
    }
    return true;
  });
});

const sortedItems = computed(()=>{
  const arr = [...filteredItems.value];
  const k = sort.key; const dir = sort.dir === "asc" ? 1 : -1;
  arr.sort((a,b)=>{
    let va = k==='route' ? `${a.departureLocation}→${a.arrivalLocation}` : a[k];
    let vb = k==='route' ? `${b.departureLocation}→${b.arrivalLocation}` : b[k];
    if (k==='departAt') { va = new Date(va).getTime(); vb = new Date(vb).getTime(); }
    if (typeof va === 'string') va = va.toLowerCase();
    if (typeof vb === 'string') vb = vb.toLowerCase();
    if (va < vb) return -1*dir; if (va > vb) return 1*dir; return 0;
  });
  return arr;
});

const totalPages = computed(()=> Math.ceil(sortedItems.value.length / pageSize.value));
const pagedItems = computed(()=> sortedItems.value.slice((page.value-1)*pageSize.value, (page.value-1)*pageSize.value + pageSize.value));

/** ====== UI actions ====== */
function openCreate(){
  isEdit.value = false;
  Object.assign(form, { id: undefined, type:"bus", carrier:"", departureLocation:"", arrivalLocation:"", departAt:"", basePrice:undefined, totalSeats:undefined, availableSeats:undefined, status:"AVAILABLE" });
  formError.value = "";
  showModal.value = true;
}
function openEditSelected(){ if (!selectedItem.value) return; openEdit(selectedItem.value); }
function openDeleteSelected(){ if (!selectedItem.value) return; confirming.value = true; }

function openEdit(it){
  isEdit.value = true;
  Object.assign(form, JSON.parse(JSON.stringify(it)));
  formError.value = "";
  showModal.value = true;
}

function openNotify(){ notifyModal.value = true; }
function closeNotify(){ notifyModal.value = false; }
function closeModal(){ showModal.value = false; }
function resetFilters(){ Object.assign(filters, { keyword:"", type:"", status:"", fromDate:"", toDate:"" }); page.value = 1; }
function sortBy(key){ if (sort.key===key) sort.dir = (sort.dir==='asc'?'desc':'asc'); else { sort.key=key; sort.dir='asc'; } }
function sortIcon(key){ if (sort.key!==key) return 'fas fa-sort text-muted'; return sort.dir==='asc' ? 'fas fa-sort-up' : 'fas fa-sort-down'; }
function goPrev(){ if (page.value>1) page.value--; }
function goNext(){ if (page.value<totalPages.value) page.value++; }

/** ====== Validation + Submit strategy ====== */
const canSubmit = computed(() =>
  !!form.carrier &&
  !!form.departureLocation &&
  !!form.arrivalLocation &&
  !!form.departAt &&
  form.basePrice >= 0 &&
  form.totalSeats >= 0 &&
  form.availableSeats >= 0 &&
  form.availableSeats <= form.totalSeats
);

function validateForm(){
  if (!canSubmit.value) return "Dữ liệu chưa hợp lệ hoặc chưa đủ.";
  return "";
}

async function onSubmit(){
  formError.value = "";
  const err = validateForm();
  if (err) { formError.value = err; return; }

  if (!API_ENABLED) {
    // UI-only: không mutate items. Đóng modal là xong.
    closeModal();
    return;
  }

  // === Có BE: call API thật ===
  if (isEdit.value && form.id) {
    await updateTrip(form.id, { ...form });
  } else {
    await createTrip({ ...form });
  }
  closeModal();
  // TODO: reload list từ API
}

async function onDelete(){
  if (!selectedId.value) { confirming.value = false; return; }
  if (!API_ENABLED) { confirming.value = false; return; }  // UI-only

  await deleteTrip(selectedId.value);
  confirming.value = false;
  // TODO: reload list từ API
}

/** ====== Helpers ====== */
function fmtCurrency(n){ if(n==null) return '—'; return n.toLocaleString('vi-VN') + ' ₫'; }
function fmtDateTime(dt){ const d = new Date(dt); return isNaN(d)?'—': d.toLocaleString('vi-VN',{hour12:false,day:'2-digit',month:'2-digit',year:'numeric',hour:'2-digit',minute:'2-digit'}); }
function typeText(t){ return t==='bus'?'Xe khách': t==='train'?'Tàu':'Máy bay'; }
function typeIcon(t){ return t==='bus'?'fas fa-bus': t==='train'?'fas fa-train':'fas fa-plane'; }
function typeBadge(t){ return t==='bus'?'badge-bus': t==='train'?'badge-train':'badge-plane'; }
function statusText(s){ return s==='AVAILABLE'?'Còn vé': s==='SOLD_OUT'?'Hết vé':'Hủy chuyến'; }
function statusBadge(s){ return s==='AVAILABLE'?'badge-available': s==='SOLD_OUT'?'badge-soldout':'badge-cancel'; }
function statusDot(s){ return s==='AVAILABLE'?'dot-green': s==='SOLD_OUT'?'dot-gray':'dot-red'; }
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

/* Badges loại */
.badge{ border-radius:999px; padding:.45rem .6rem; font-weight:700; letter-spacing:.2px; }
.badge-bus{ background:#eaf6ff; color:#0a6ebd; }
.badge-train{ background:#e8fbf6; color:#0b9d77; }
.badge-plane{ background:#fff4e5; color:#b4690e; }

/* Trạng thái */
.status-dot{ display:inline-block; width:8px; height:8px; border-radius:50%; vertical-align:middle; }
.dot-green{ background:#3add76; } .dot-gray{ background:#9ca3af; } .dot-red{ background:#ef4444; }
.badge-available{ background:#e8fbf2; color:#137b52; }
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
