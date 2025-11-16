<template>
  <div class="change-password-page">
    <div class="card shadow">
      <h3>Đổi mật khẩu</h3>
      <p class="text-muted">Cập nhật mật khẩu mới để bảo vệ tài khoản của bạn.</p>
      <form @submit.prevent="handleSubmit">
        <div class="form-group">
          <label>Mật khẩu hiện tại</label>
          <input type="password" v-model.trim="form.currentPassword" required />
        </div>
        <div class="form-group">
          <label>Mật khẩu mới</label>
          <input type="password" v-model.trim="form.newPassword" required minlength="6" />
        </div>
        <div class="form-group">
          <label>Nhập lại mật khẩu mới</label>
          <input type="password" v-model.trim="form.confirmPassword" required />
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
        this.form.currentPassword = ''
        this.form.newPassword = ''
        this.form.confirmPassword = ''
      } catch (error) {
        this.errorMessage =
          error.response?.data?.message ||
          Object.values(error.response?.data?.errors || {})?.[0]?.[0] ||
          'Không thể đổi mật khẩu. Vui lòng thử lại.'
      } finally {
        this.isSubmitting = false
      }
    },
  },
}
</script>

<style scoped>
.change-password-page {
  min-height: 60vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 40px 16px;
  background: radial-gradient(circle at top, rgba(59, 130, 246, 0.08), transparent 60%);
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
.form-group input {
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
