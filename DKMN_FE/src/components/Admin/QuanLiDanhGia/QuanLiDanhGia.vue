<template>
  <div class="quan-ly-danh-gia card p-4 shadow-sm position-relative">
    <div class="header-bar d-flex align-items-center justify-content-between mb-3">
      <h4 class="page-title m-0">
        <i class="fas fa-star-half-alt me-2"></i> Quản lý đánh giá & phản hồi
      </h4>
      <div class="d-flex gap-2">
        <button class="btn btn-approve" @click="approveSelected">
          <i class="fas fa-check me-1"></i> Duyệt
        </button>
        <button class="btn btn-hide" @click="hideSelected">
          <i class="fas fa-eye-slash me-1"></i> Ẩn
        </button>
        <button class="btn btn-delete" @click="deleteSelected">
          <i class="fas fa-trash-alt me-1"></i> Xóa
        </button>
        <button class="btn btn-outline-secondary" @click="fetchReviews">
          <i class="fas fa-sync-alt me-1"></i> Tải lại
        </button>
      </div>
    </div>
    <div class="row g-2 mb-3">
      <div class="col-md-4">
        <select class="form-select" v-model="filterRating">
          <option value="">-- Lọc theo điểm sao --</option>
          <option value="5">5 sao</option>
          <option value="4">4 sao</option>
          <option value="3">3 sao</option>
          <option value="2">2 sao</option>
          <option value="1">1 sao</option>
        </select>
      </div>
      <div class="col-md-4">
        <select class="form-select" v-model="filterStatus">
          <option value="">-- Lọc theo trạng thái --</option>
          <option value="cho_duyet">Chờ duyệt</option>
          <option value="chap_nhan">Hiển thị</option>
          <option value="tu_choi">Đã ẩn</option>
        </select>
      </div>
      <div class="col-md-4">
        <input
          v-model.trim="searchKeyword"
          type="text"
          class="form-control"
          placeholder="Tìm theo khách, tuyến hoặc nội dung"
        />
      </div>
    </div>

    <div v-if="errorMessage" class="alert alert-danger py-2">{{ errorMessage }}</div>

    <div class="table-responsive">
      <table class="table table-hover align-middle">
        <thead class="table-light border-bottom">
          <tr>
            <th style="width:40px">
              <input type="checkbox" v-model="selectAll" @change="toggleSelectAll" />
            </th>
            <th>ID</th>
            <th>Điểm</th>
            <th>Nội dung</th>
            <th>Khách hàng</th>
            <th>Chuyến đi</th>
            <th>Trạng thái</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="!reviews.length">
            <td colspan="7" class="text-center text-muted py-4">
              <i class="far fa-folder-open fa-lg mb-2"></i><br />
              Chưa có đánh giá nào phù hợp.
            </td>
          </tr>
          <tr v-for="rev in reviews" :key="rev.id">
            <td><input type="checkbox" v-model="selected" :value="rev.id" /></td>
            <td>#{{ rev.id }}</td>
            <td>
              <span class="text-warning me-1">
                <i v-for="n in rev.rating" :key="n" class="bx bxs-star"></i>
              </span>
              <span class="text-muted">{{ rev.rating }}/5</span>
            </td>
            <td>
              <div class="text-dark fw-semibold">{{ rev.comment || '—' }}</div>
              <small class="text-muted">{{ formatDate(rev.createdAt) }}</small>
            </td>
            <td>{{ rev.customer }}</td>
            <td>{{ rev.trip || '—' }}</td>
            <td>
              <span :class="['badge', statusBadgeClass(rev.status)]">
                {{ statusLabel(rev.status) }}
              </span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-3" v-if="pagination.total">
      <small class="text-muted">
        Trang {{ pagination.currentPage }} / {{ pagination.lastPage }} — {{ pagination.total }} đánh giá
      </small>
      <div class="d-flex gap-2">
        <button class="btn btn-sm btn-outline-secondary" :disabled="pagination.currentPage <= 1 || loading" @click="fetchReviews(pagination.currentPage - 1)">
          Trước
        </button>
        <button class="btn btn-sm btn-outline-secondary" :disabled="pagination.currentPage >= pagination.lastPage || loading" @click="fetchReviews(pagination.currentPage + 1)">
          Sau
        </button>
      </div>
    </div>

    <div v-if="loading" class="loading-overlay">
      <div class="spinner-border text-primary" role="status"></div>
      <span class="ms-2">Đang tải...</span>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import api from '../../../services/api'

const reviews = ref([])
const selected = ref([])
const selectAll = ref(false)
const filterRating = ref('')
const filterStatus = ref('')
const searchKeyword = ref('')
const loading = ref(false)
const errorMessage = ref('')
const pagination = ref({
  currentPage: 1,
  lastPage: 1,
  total: 0,
})

const statusMap = {
  cho_duyet: 'Chờ duyệt',
  chap_nhan: 'Hiển thị',
  tu_choi: 'Đã ẩn',
}
const statusBadgeClass = (status) =>
  ({
    cho_duyet: 'bg-warning text-dark',
    chap_nhan: 'bg-success',
    tu_choi: 'bg-secondary',
  }[status] || 'bg-secondary')
const statusLabel = (status) => statusMap[status] || 'Chờ duyệt'

const formatDate = (value) => (value ? new Date(value).toLocaleString('vi-VN') : '—')

const fetchReviews = async (page = 1) => {
  loading.value = true
  errorMessage.value = ''
  try {
    const { data } = await api.get('/admin/ratings', {
      params: {
        page,
        rating: filterRating.value || undefined,
        status: filterStatus.value || undefined,
        search: searchKeyword.value || undefined,
      },
    })
    reviews.value = data?.data || []
    pagination.value = {
      currentPage: data?.meta?.currentPage || data?.meta?.current_page || page,
      lastPage: data?.meta?.lastPage || data?.meta?.last_page || 1,
      total: data?.meta?.total || reviews.value.length,
    }
    selectAll.value = false
    selected.value = []
  } catch (error) {
    const errorMsg = error.response?.data?.message || 'Không thể tải danh sách đánh giá.'
    errorMessage.value = errorMsg
    window.$toast?.error?.(errorMsg)
  } finally {
    loading.value = false
  }
}

const toggleSelectAll = () => {
  if (selectAll.value) {
    selected.value = reviews.value.map((r) => r.id)
  } else {
    selected.value = []
  }
}

const requireSelection = () => {
  if (!selected.value.length) {
    alert('Vui lòng chọn ít nhất một đánh giá.')
    return false
  }
  return true
}

const updateStatus = async (status) => {
  if (!requireSelection()) return
  loading.value = true
  try {
    await Promise.all(
      selected.value.map((id) => api.patch(`/admin/ratings/${id}`, { status }))
    )
    const msg = status === 'chap_nhan' ? 'Đã duyệt đánh giá! ✅' : 'Đã ẩn đánh giá! 👁️'
    window.$toast?.success?.(msg)
    await fetchReviews(pagination.value.currentPage)
  } catch (error) {
    const errorMsg = error.response?.data?.message || 'Không thể cập nhật trạng thái.'
    errorMessage.value = errorMsg
    window.$toast?.error?.(errorMsg)
  } finally {
    loading.value = false
  }
}

const approveSelected = () => updateStatus('chap_nhan')
const hideSelected = () => updateStatus('tu_choi')

const deleteSelected = async () => {
  if (!requireSelection()) return
  if (!confirm('Bạn chắc chắn muốn xóa các đánh giá đã chọn?')) return
  loading.value = true
  try {
    await Promise.all(selected.value.map((id) => api.delete(`/admin/ratings/${id}`)))
    window.$toast?.success?.('Đã xóa đánh giá thành công! 🗑️')
    await fetchReviews(pagination.value.currentPage)
  } catch (error) {
    const errorMsg = error.response?.data?.message || 'Không thể xóa đánh giá.'
    errorMessage.value = errorMsg
    window.$toast?.error?.(errorMsg)
  } finally {
    loading.value = false
  }
}

onMounted(() => fetchReviews())
watch([filterRating, filterStatus, searchKeyword], () => fetchReviews(), { flush: 'post' })
</script>

<style scoped>
:root {
  --dk-blue: #0b3b6e;
  --dk-blue-600: #0a3563;
}

.page-title {
  color: var(--dk-blue);
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 8px;
}
.header-bar {
  border-bottom: 2px solid #eef2f6;
  padding-bottom: 10px;
}

.action-buttons .btn,
.btn-approve,
.btn-hide,
.btn-delete {
  min-width: 86px;
  padding: 6px 12px;
  border-radius: 8px;
  color: #fff;
  border: none;
  box-shadow: 0 2px 6px rgba(16, 24, 40, 0.08);
  transition: transform 0.12s ease, box-shadow 0.12s ease;
}
.btn-approve {
  background: linear-gradient(90deg, #2ec4b6, #1fa3a0);
}
.btn-hide {
  background: linear-gradient(90deg, #ffb86b, #ff9e49);
  color: #111;
}
.btn-delete {
  background: linear-gradient(90deg, #ff6b6b, #ff4c4c);
}
.btn-approve:hover,
.btn-hide:hover,
.btn-delete:hover {
  transform: translateY(-1px);
  box-shadow: 0 6px 18px rgba(15, 23, 42, 0.15);
}

.table thead th {
  color: var(--dk-blue);
  font-weight: 600;
  white-space: nowrap;
}
.table tbody td {
  vertical-align: middle;
}

.badge {
  border-radius: 999px;
  padding: 4px 10px;
  font-weight: 500;
}

.loading-overlay {
  position: absolute;
  inset: 0;
  background: rgba(255, 255, 255, 0.7);
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 10px;
}
</style>
