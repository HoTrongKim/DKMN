<template>
  <div class="qllh-page">
    <div class="card p-4 shadow-sm rounded-3">
      <!-- ===== HEADER ===== -->
      <div class="header-bar d-flex align-items-center justify-content-between flex-wrap mb-3">
        <h4 class="page-title m-0">
          <i class="fas fa-envelope me-2"></i> Quản Lý Tin Nhắn Liên Hệ
        </h4>
        <div class="d-flex flex-wrap gap-2">
          <button
            type="button"
            class="btn btn-grad-edit"
            :disabled="!selectedId || actionLoading"
            @click="openReplyModal"
          >
            <i class="fas fa-reply me-1"></i> Trả lời
          </button>
          <button
            type="button"
            class="btn btn-grad-del"
            :disabled="!selectedId || actionLoading"
            @click="deleteSelected"
          >
            <i class="fas fa-trash me-1"></i> Xóa
          </button>
          <button
            type="button"
            class="btn btn-grad-refresh"
            :disabled="isLoading"
            @click="fetchMessages"
          >
            <span v-if="isLoading" class="spinner-border spinner-border-sm me-1"></span>
            <i v-else class="fas fa-rotate me-1"></i> Tải lại
          </button>
        </div>
      </div>

      <!-- ===== BỘ LỌC ===== -->
      <div class="row g-2 mb-3 filter-panel">
        <div class="col-12 col-md-4">
          <div class="input-group">
            <span class="input-group-text"><i class="fas fa-search"></i></span>
            <input
              v-model="filters.keyword"
              @input="fetchMessages"
              class="form-control"
              placeholder="Tìm theo tên / email / chủ đề"
            />
          </div>
        </div>

        <div class="col-6 col-md-3">
          <select class="form-select" v-model="filters.trang_thai" @change="fetchMessages">
            <option value="">Tất cả trạng thái</option>
            <option value="moi">Mới</option>
            <option value="dang_xu_ly">Đang xử lý</option>
            <option value="da_tra_loi">Đã trả lời</option>
            <option value="dong">Đóng</option>
          </select>
        </div>
      </div>

      <div v-if="actionError" class="alert alert-danger py-2 px-3">
        {{ actionError }}
      </div>
      <div v-else-if="actionMessage" class="alert alert-success py-2 px-3">
        {{ actionMessage }}
      </div>

      <!-- ===== BẢNG ===== -->
      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead class="table-light border-bottom">
            <tr>
              <th style="width:42px;"></th>
              <th>Người gửi</th>
              <th>Email</th>
              <th>SĐT</th>
              <th>Chủ đề</th>
              <th>Nội dung</th>
              <th>Trạng thái</th>
              <th>Ngày gửi</th>
              <th class="text-center">Thao tác</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(msg, index) in messages"
              :key="msg.id"
              :class="{ 'row-selected': msg.id === selectedId }"
              @click="selectMessage(msg.id)"
              style="cursor:pointer"
            >
              <td class="text-center">
                <input
                  type="radio"
                  :checked="msg.id === selectedId"
                  @click.stop="selectMessage(msg.id)"
                />
              </td>
              <td>
                <strong>{{ msg.ho_ten }}</strong>
              </td>
              <td>{{ msg.email }}</td>
              <td>{{ msg.so_dien_thoai || '--' }}</td>
              <td>
                <span class="badge bg-info-subtle text-info">{{ msg.chu_de }}</span>
              </td>
              <td>
                <div class="text-truncate" style="max-width: 200px;" :title="msg.noi_dung">
                  {{ msg.noi_dung }}
                </div>
              </td>
              <td>
                <span :class="statusBadgeClass(msg.trang_thai)">{{ statusLabel(msg.trang_thai) }}</span>
              </td>
              <td>
                <small>{{ formatDate(msg.ngay_tao) }}</small>
              </td>
              <td class="text-center">
                <button
                  class="btn btn-sm btn-outline-primary"
                  @click.stop="viewMessage(msg)"
                  title="Xem chi tiết"
                >
                  <i class="fas fa-eye"></i>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="!messages.length && !isLoading" class="text-center py-5 text-muted">
        <i class="fas fa-inbox fa-3x mb-3"></i>
        <p>Chưa có tin nhắn liên hệ nào</p>
      </div>
    </div>

    <!-- Modal Xem chi tiết -->
    <div
      v-if="viewModal.visible"
      class="modal-backdrop"
      @click="closeViewModal"
    >
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="fas fa-envelope me-2"></i> Chi tiết tin nhắn
          </h5>
          <button type="button" class="btn-close" @click="closeViewModal"></button>
        </div>
        <div class="modal-body" v-if="viewModal.message">
          <div class="message-detail">
            <div class="detail-row">
              <span class="detail-label">Người gửi:</span>
              <span class="detail-value">{{ viewModal.message.ho_ten }}</span>
            </div>
            <div class="detail-row">
              <span class="detail-label">Email:</span>
              <span class="detail-value">
                <a :href="`mailto:${viewModal.message.email}`">{{ viewModal.message.email }}</a>
              </span>
            </div>
            <div class="detail-row" v-if="viewModal.message.so_dien_thoai">
              <span class="detail-label">Số điện thoại:</span>
              <span class="detail-value">
                <a :href="`tel:${viewModal.message.so_dien_thoai}`">{{ viewModal.message.so_dien_thoai }}</a>
              </span>
            </div>
            <div class="detail-row">
              <span class="detail-label">Chủ đề:</span>
              <span class="detail-value">
                <span class="badge bg-info-subtle text-info">{{ viewModal.message.chu_de }}</span>
              </span>
            </div>
            <div class="detail-row">
              <span class="detail-label">Trạng thái:</span>
              <span class="detail-value">
                <span :class="statusBadgeClass(viewModal.message.trang_thai)">
                  {{ statusLabel(viewModal.message.trang_thai) }}
                </span>
              </span>
            </div>
            <div class="detail-row">
              <span class="detail-label">Ngày gửi:</span>
              <span class="detail-value">{{ formatDate(viewModal.message.ngay_tao) }}</span>
            </div>
            <div class="detail-row">
              <span class="detail-label">Nội dung:</span>
              <div class="detail-value message-content">{{ viewModal.message.noi_dung }}</div>
            </div>
            <div class="detail-row" v-if="viewModal.message.tra_loi">
              <span class="detail-label">Trả lời:</span>
              <div class="detail-value message-reply">{{ viewModal.message.tra_loi }}</div>
            </div>
            <div class="detail-row" v-if="viewModal.message.ngay_tra_loi">
              <span class="detail-label">Ngày trả lời:</span>
              <span class="detail-value">{{ formatDate(viewModal.message.ngay_tra_loi) }}</span>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" @click="closeViewModal">Đóng</button>
          <button
            type="button"
            class="btn btn-primary"
            @click="openReplyModal"
            v-if="viewModal.message && viewModal.message.trang_thai !== 'da_tra_loi'"
          >
            <i class="fas fa-reply me-1"></i> Trả lời
          </button>
        </div>
      </div>
    </div>

    <!-- Modal Trả lời -->
    <div
      v-if="replyModal.visible"
      class="modal-backdrop"
      @click="closeReplyModal"
    >
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="fas fa-reply me-2"></i> Trả lời tin nhắn
          </h5>
          <button type="button" class="btn-close" @click="closeReplyModal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-semibold">Trạng thái</label>
            <select class="form-select" v-model="replyForm.trang_thai">
              <option value="dang_xu_ly">Đang xử lý</option>
              <option value="da_tra_loi">Đã trả lời</option>
              <option value="dong">Đóng</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Nội dung trả lời</label>
            <textarea
              class="form-control"
              v-model="replyForm.tra_loi"
              rows="5"
              placeholder="Nhập nội dung trả lời cho khách hàng..."
            ></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" @click="closeReplyModal">Hủy</button>
          <button
            type="button"
            class="btn btn-primary"
            @click="submitReply"
            :disabled="actionLoading"
          >
            <span v-if="actionLoading" class="spinner-border spinner-border-sm me-1"></span>
            <i v-else class="fas fa-paper-plane me-1"></i>
            Gửi trả lời
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import api from '../../../services/api'

export default {
  name: 'QuanLiLienHe',
  data() {
    return {
      messages: [],
      selectedId: null,
      isLoading: false,
      actionLoading: false,
      actionError: '',
      actionMessage: '',
      filters: {
        keyword: '',
        trang_thai: ''
      },
      viewModal: {
        visible: false,
        message: null
      },
      replyModal: {
        visible: false
      },
      replyForm: {
        trang_thai: 'da_tra_loi',
        tra_loi: ''
      }
    }
  },
  mounted() {
    this.fetchMessages()
  },
  methods: {
    async fetchMessages() {
      this.isLoading = true
      this.actionError = ''
      try {
        const params = {}
        if (this.filters.keyword) {
          params.keyword = this.filters.keyword
        }
        if (this.filters.trang_thai) {
          params.trang_thai = this.filters.trang_thai
        }
        
        const response = await api.get('/dkmn/lien-he/get-data', { params })
        this.messages = response.data?.data || []
      } catch (error) {
        this.actionError = 'Không thể tải danh sách tin nhắn liên hệ'
        console.error('Fetch messages error:', error)
      } finally {
        this.isLoading = false
      }
    },
    selectMessage(id) {
      this.selectedId = id === this.selectedId ? null : id
    },
    viewMessage(message) {
      this.viewModal.message = message
      this.viewModal.visible = true
      this.selectedId = message.id
    },
    closeViewModal() {
      this.viewModal.visible = false
      this.viewModal.message = null
    },
    openReplyModal() {
      if (!this.selectedId) {
        this.actionError = 'Vui lòng chọn tin nhắn cần trả lời'
        return
      }
      const message = this.messages.find(m => m.id === this.selectedId)
      if (message) {
        this.viewModal.message = message
        this.replyForm.tra_loi = message.tra_loi || ''
        this.replyForm.trang_thai = message.trang_thai === 'moi' ? 'da_tra_loi' : message.trang_thai
      }
      this.replyModal.visible = true
    },
    closeReplyModal() {
      this.replyModal.visible = false
      this.replyForm = {
        trang_thai: 'da_tra_loi',
        tra_loi: ''
      }
    },
    async submitReply() {
      if (!this.selectedId) {
        this.actionError = 'Vui lòng chọn tin nhắn cần trả lời'
        return
      }
      
      this.actionLoading = true
      this.actionError = ''
      this.actionMessage = ''
      
      try {
        const response = await api.put(`/dkmn/lien-he/${this.selectedId}`, this.replyForm)
        if (response.data?.status) {
          this.actionMessage = response.data.message || 'Đã cập nhật tin nhắn thành công'
          this.closeReplyModal()
          this.closeViewModal()
          await this.fetchMessages()
          setTimeout(() => {
            this.actionMessage = ''
          }, 3000)
        }
      } catch (error) {
        this.actionError = error.response?.data?.message || 'Không thể cập nhật tin nhắn'
      } finally {
        this.actionLoading = false
      }
    },
    async deleteSelected() {
      if (!this.selectedId) {
        this.actionError = 'Vui lòng chọn tin nhắn cần xóa'
        return
      }
      
      if (!confirm('Bạn có chắc chắn muốn xóa tin nhắn này?')) {
        return
      }
      
      this.actionLoading = true
      this.actionError = ''
      this.actionMessage = ''
      
      try {
        const response = await api.delete(`/dkmn/lien-he/${this.selectedId}`)
        if (response.data?.status) {
          this.actionMessage = 'Đã xóa tin nhắn thành công'
          this.selectedId = null
          await this.fetchMessages()
          setTimeout(() => {
            this.actionMessage = ''
          }, 3000)
        }
      } catch (error) {
        this.actionError = error.response?.data?.message || 'Không thể xóa tin nhắn'
      } finally {
        this.actionLoading = false
      }
    },
    statusLabel(status) {
      const map = {
        moi: 'Mới',
        dang_xu_ly: 'Đang xử lý',
        da_tra_loi: 'Đã trả lời',
        dong: 'Đóng'
      }
      return map[status] || status
    },
    statusBadgeClass(status) {
      const map = {
        moi: 'badge bg-warning text-dark',
        dang_xu_ly: 'badge bg-info',
        da_tra_loi: 'badge bg-success',
        dong: 'badge bg-secondary'
      }
      return map[status] || 'badge bg-secondary'
    },
    formatDate(date) {
      if (!date) return '--'
      try {
        return new Date(date).toLocaleString('vi-VN')
      } catch (e) {
        return date
      }
    }
  }
}
</script>

<style scoped>
.qllh-page {
  padding: 1rem;
}

.header-bar {
  border-bottom: 1px solid #e2e8f0;
  padding-bottom: 1rem;
}

.page-title {
  font-weight: 700;
  color: #1e293b;
}

.filter-panel {
  background: #f8fafc;
  padding: 1rem;
  border-radius: 8px;
}

.table th {
  font-weight: 600;
  color: #475569;
  background: #f8fafc;
}

.row-selected {
  background-color: #e0f2fe !important;
}

.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1050;
  padding: 1rem;
}

.modal-content {
  background: #ffffff;
  border-radius: 12px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  max-width: 700px;
  width: 100%;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-header {
  padding: 1.5rem;
  border-bottom: 1px solid #e2e8f0;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-title {
  margin: 0;
  font-weight: 700;
}

.modal-body {
  padding: 1.5rem;
}

.modal-footer {
  padding: 1rem 1.5rem;
  border-top: 1px solid #e2e8f0;
  display: flex;
  justify-content: flex-end;
  gap: 0.5rem;
}

.message-detail {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.detail-row {
  display: flex;
  gap: 1rem;
  align-items: flex-start;
}

.detail-label {
  font-weight: 600;
  color: #475569;
  min-width: 120px;
}

.detail-value {
  color: #1e293b;
  flex: 1;
}

.message-content,
.message-reply {
  background: #f8fafc;
  padding: 1rem;
  border-radius: 8px;
  white-space: pre-wrap;
  word-break: break-word;
}

.message-reply {
  background: #ecfdf5;
  border-left: 3px solid #10b981;
}

.btn-grad-refresh {
  background: linear-gradient(135deg, #3b82f6, #8b5cf6);
  color: #ffffff;
  border: none;
}

.btn-grad-edit {
  background: linear-gradient(135deg, #10b981, #34d399);
  color: #ffffff;
  border: none;
}

.btn-grad-del {
  background: linear-gradient(135deg, #ef4444, #f87171);
  color: #ffffff;
  border: none;
}

.btn-grad-refresh:hover,
.btn-grad-edit:hover,
.btn-grad-del:hover {
  opacity: 0.9;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}
</style>

