<template>
  <div class="admin-security">
    <h2 class="page-title">Bảo mật & mật khẩu</h2>
    <div class="security-card">
      <section class="card">
        <h3>Đổi mật khẩu</h3>
        <p class="text-muted">Cập nhật mật khẩu mạnh để bảo vệ tài khoản của bạn.</p>

        <div class="form-group">
          <label>Mật khẩu hiện tại</label>
          <div class="input-group">
            <input
              :type="showCurrent ? 'text' : 'password'"
              v-model="form.currentPassword"
              autocomplete="current-password"
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
              v-model="form.newPassword"
              minlength="6"
              autocomplete="new-password"
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
              v-model="form.confirmPassword"
              autocomplete="new-password"
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

        <button class="btn btn-primary" @click="changePassword" :disabled="isSubmitting">
          <span v-if="isSubmitting" class="spinner-border spinner-border-sm me-2"></span>
          <i v-else class="fas fa-lock me-1"></i>
          {{ isSubmitting ? 'Đang cập nhật...' : 'Cập nhật mật khẩu' }}
        </button>
      </section>
    </div>
  </div>
</template>

<script>
import api from '../../../services/api'

export default {
  name: 'AdminSecurity',
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
    }
  },
  methods: {
    async changePassword() {
      if (!this.form.currentPassword || !this.form.newPassword) {
        return this.$toast?.error?.('Vui lòng nhập đầy đủ thông tin.');
      }
      if (this.form.newPassword !== this.form.confirmPassword) {
        return this.$toast?.error?.('Mật khẩu mới không khớp.');
      }
      this.isSubmitting = true;
      try {
        await api.post('/dkmn/me/change-password', {
          currentPassword: this.form.currentPassword,
          newPassword: this.form.newPassword,
          confirmPassword: this.form.confirmPassword,
        });
        this.$toast?.success?.('Đã cập nhật mật khẩu.');
        this.form.currentPassword = '';
        this.form.newPassword = '';
        this.form.confirmPassword = '';
        this.showCurrent = false;
        this.showNew = false;
        this.showConfirm = false;
      } catch (error) {
        const message =
          error.response?.data?.message ||
          Object.values(error.response?.data?.errors || {})?.[0]?.[0] ||
          'Không thể đổi mật khẩu.';
        this.$toast?.error?.(message);
      } finally {
        this.isSubmitting = false;
      }
    },
  },
}
</script>

<style scoped>
.admin-security {
  max-width: 1000px;
  margin: 0 auto;
}
.page-title {
  font-size: 1.5rem;
  font-weight: 700;
  margin-bottom: 24px;
  color: #0f172a;
}
.security-card {
  max-width: 640px;
}
.card {
  background: #fff;
  border-radius: 20px;
  padding: 24px;
  box-shadow: 0 20px 40px rgba(15, 23, 42, 0.08);
}
.card h3 {
  margin: 0 0 10px;
  font-size: 1.1rem;
}
.text-muted {
  color: #64748b;
  margin-bottom: 20px;
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
  gap: 8px;
  align-items: center;
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
  border-color: #0ea5e9;
  outline: none;
  box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.15);
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
  border-color: #0ea5e9;
  color: #0ea5e9;
}
.btn {
  border: none;
  border-radius: 12px;
  padding: 12px 18px;
  font-weight: 600;
  cursor: pointer;
}
.btn-primary {
  background: linear-gradient(120deg, #0ea5e9, #2563eb);
  color: #fff;
  box-shadow: 0 12px 25px rgba(14, 165, 233, 0.25);
}
.btn-outline {
  border: 1px solid #d0d5dd;
  background: #fff;
  color: #0f172a;
  padding: 8px 14px;
}
.btn-danger {
  background: #ef4444;
  color: #fff;
  margin-top: 16px;
}
.session-list {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 12px;
}
session-list li {
  display: flex;
  justify-content: space-between;
  align-items: center;
  border: 1px solid #e0e7ff;
  padding: 12px 14px;
  border-radius: 14px;
}
.session-list p {
  margin: 4px 0 0;
  color: #64748b;
  font-size: 0.85rem;
}
@media (max-width: 767px) {
.session-list li {
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
  }
}
</style>
