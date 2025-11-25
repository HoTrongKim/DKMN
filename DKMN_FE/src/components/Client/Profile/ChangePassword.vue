<template>
  <div class="change-password-page">
    <div class="card shadow">
      <h3>Đổi mật khẩu</h3>
      <p class="text-muted">Cập nhật mật khẩu mới để bảo vệ tài khoản của bạn.</p>
      <form @submit.prevent="handleSubmit">
        <div class="form-group">
          <label>Mật khẩu hiện tại</label>
          <div class="input-group">
            <input
              :type="showCurrent ? 'text' : 'password'"
              v-model.trim="form.currentPassword"
              required
            />
            <button
              type="button"
              class="toggle-visibility"
              @click="showCurrent = !showCurrent"
              :aria-label="showCurrent ? 'Ẩn mật khẩu hiện tại' : 'Hiển thị mật khẩu hiện tại'"
            >
              <i :class="showCurrent ? 'bx bx-show' : 'bx bx-hide'"></i>
            </button>
          </div>
        </div>
        <div class="form-group">
          <label>Mật khẩu mới</label>
          <div class="input-group">
            <input
              :type="showNew ? 'text' : 'password'"
              v-model.trim="form.newPassword"
              required
              minlength="6"
            />
            <button
              type="button"
              class="toggle-visibility"
              @click="showNew = !showNew"
              :aria-label="showNew ? 'Ẩn mật khẩu mới' : 'Hiển thị mật khẩu mới'"
            >
              <i :class="showNew ? 'bx bx-show' : 'bx bx-hide'"></i>
            </button>
          </div>
        </div>
        <div class="form-group">
          <label>Nhập lại mật khẩu mới</label>
          <div class="input-group">
            <input
              :type="showConfirm ? 'text' : 'password'"
              v-model.trim="form.confirmPassword"
              required
            />
            <button
              type="button"
              class="toggle-visibility"
              @click="showConfirm = !showConfirm"
              :aria-label="showConfirm ? 'Ẩn mật khẩu xác nhận' : 'Hiển thị mật khẩu xác nhận'"
            >
              <i :class="showConfirm ? 'bx bx-show' : 'bx bx-hide'"></i>
            </button>
          </div>
        </div>
        <div v-if="errorMessage" class="alert alert-danger">{{ errorMessage }}</div>
        <div v-if="successMessage" class="alert alert-success">{{ successMessage }}</div>
        <button class="btn btn-primary w-100" type="submit" :disabled="isSubmitting">
          <span v-if="isSubmitting" class="spinner-border spinner-border-sm me-2"></span>
          Cập nhật mật khẩu
        </button>
      </form>
    </div>
  </div>
</template>

<script>
import api from '../../../services/api'

export default {
  name: 'ClientChangePassword',
  data() {
    return {
      form: {
        currentPassword: '',
        newPassword: '',
        confirmPassword: '',
      },
      showCurrent: false,
      showNew: false,
      showConfirm: false,
      isSubmitting: false,
      errorMessage: '',
      successMessage: '',
    }
  },
  methods: {
    async handleSubmit() {
      this.errorMessage = ''
      this.successMessage = ''

      if (this.form.newPassword !== this.form.confirmPassword) {
        this.errorMessage = 'Mật khẩu mới không khớp.'
        this.$toast?.error(this.errorMessage)
        return
      }

      if (this.form.newPassword.length < 6) {
        this.errorMessage = 'Mật khẩu mới phải có ít nhất 6 ký tự.'
        this.$toast?.error(this.errorMessage)
        return
      }

      this.isSubmitting = true
      try {
        await api.post('/dkmn/me/change-password', {
          currentPassword: this.form.currentPassword,
          newPassword: this.form.newPassword,
          confirmPassword: this.form.confirmPassword,
        })
        this.successMessage = 'Đã cập nhật mật khẩu thành công.'
        this.$toast?.success(this.successMessage + ' 🔒')
        this.form.currentPassword = ''
        this.form.newPassword = ''
        this.form.confirmPassword = ''
        this.showCurrent = false
        this.showNew = false
        this.showConfirm = false
      } catch (error) {
        this.errorMessage =
          error.response?.data?.message ||
          Object.values(error.response?.data?.errors || {})?.[0]?.[0] ||
          'Không thể đổi mật khẩu. Vui lòng thử lại.'
        this.$toast?.error(this.errorMessage)
      } finally {
        this.isSubmitting = false
      }
    },
  },
}
</script>

<style scoped>
.change-password-page {
  min-height: calc(100vh - 180px);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 120px 16px 180px;
  background: radial-gradient(circle at 20% 18%, rgba(37, 99, 235, 0.18), transparent 42%),
    radial-gradient(circle at 82% 12%, rgba(14, 165, 233, 0.16), transparent 38%),
    linear-gradient(135deg, #0b1224, #0f172a 48%, #0b1224);
}
.card {
  width: 100%;
  max-width: 480px;
  background: #fff;
  border-radius: 20px;
  padding: 28px;
  border: none;
}
.card h3 {
  margin-bottom: 12px;
  font-weight: 700;
  color: #0f172a;
}
.text-muted {
  margin-bottom: 20px;
  color: #64748b;
}
.form-group {
  margin-bottom: 16px;
  display: flex;
  flex-direction: column;
}
.form-group label {
  font-weight: 600;
  margin-bottom: 6px;
  color: #475467;
}
.input-group {
  display: flex;
  align-items: center;
  gap: 8px;
}
.form-group input {
  flex: 1;
  border: 1px solid #d0d5dd;
  border-radius: 12px;
  padding: 10px 12px;
  font-size: 0.95rem;
  transition: border 0.2s;
}
.form-group input:focus {
  border-color: #2563eb;
  outline: none;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
}
.toggle-visibility {
  border: 1px solid #d0d5dd;
  background: #f8fafc;
  border-radius: 12px;
  padding: 9px 10px;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  color: #475467;
}
.toggle-visibility:hover {
  border-color: #2563eb;
  color: #2563eb;
}
.btn {
  border-radius: 12px;
  padding: 12px;
  font-weight: 600;
}
.alert {
  border-radius: 12px;
  padding: 10px 12px;
  font-size: 0.9rem;
}
.alert-danger {
  background: #fee2e2;
  color: #991b1b;
  border: none;
}
.alert-success {
  background: #dcfce7;
  color: #166534;
  border: none;
}
</style>
