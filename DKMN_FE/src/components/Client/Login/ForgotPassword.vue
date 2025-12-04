<template>
  <section class="reset-wrapper">
    <div class="reset-card">
      <div class="reset-header">
        <div class="reset-icon">
          <i class="bx bx-key"></i>
        </div>
        <div>
          <h3 class="reset-title">Quên <span>mật khẩu</span></h3>
          <p class="reset-sub">Nhập email để nhận mã OTP và đặt lại mật khẩu.</p>
          <p class="reset-link">Đã nhớ mật khẩu? <router-link to="/client-login">Đăng nhập</router-link></p>
        </div>
      </div>

      <div class="reset-body">
        <label class="form-label">Email</label>
        <div class="input-group mb-3">
          <span class="input-group-text bg-transparent">
            <i class="fa-solid fa-envelope"></i>
          </span>
          <input
            v-model.trim="email"
            type="email"
            class="form-control border-start-0"
            placeholder="Email đã đăng ký"
            autocomplete="email"
          />
        </div>

        <transition name="fade">
          <div v-if="step !== 'request'" class="mb-3">
            <label class="form-label">Mã OTP</label>
            <input
              v-model.trim="otp"
              type="text"
              class="form-control"
              placeholder="Nhập mã OTP 6 ký tự"
              maxlength="6"
              :readonly="step === 'reset'"
            />
            <small class="text-muted">Mã có hiệu lực trong 10 phút, không chia sẻ cho bất kỳ ai.</small>
          </div>
        </transition>

        <transition name="fade">
          <div v-if="step === 'reset'" class="row g-3 mb-2">
            <div class="col-12 col-md-6">
              <label class="form-label">Mật khẩu mới</label>
              <div class="input-group password-toggle">
                <input
                  v-model="password"
                  :type="showPassword ? 'text' : 'password'"
                  class="form-control"
                  placeholder="Mật khẩu mới"
                  autocomplete="new-password"
                />
                <button
                  type="button"
                  class="input-group-text toggle-visibility"
                  @click="showPassword = !showPassword"
                  :aria-label="showPassword ? 'Ẩn mật khẩu mới' : 'Hiển thị mật khẩu mới'"
                >
                  <i :class="showPassword ? 'bx bx-show' : 'bx bx-hide'"></i>
                </button>
              </div>
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label">Xác nhận mật khẩu</label>
              <div class="input-group password-toggle">
                <input
                  v-model="password_confirmation"
                  :type="showPasswordConfirm ? 'text' : 'password'"
                  class="form-control"
                  placeholder="Nhập lại mật khẩu"
                  autocomplete="new-password"
                />
                <button
                  type="button"
                  class="input-group-text toggle-visibility"
                  @click="showPasswordConfirm = !showPasswordConfirm"
                  :aria-label="showPasswordConfirm ? 'Ẩn mật khẩu xác nhận' : 'Hiển thị mật khẩu xác nhận'"
                >
                  <i :class="showPasswordConfirm ? 'bx bx-show' : 'bx bx-hide'"></i>
                </button>
              </div>
            </div>
          </div>
        </transition>

        <div class="d-grid mb-2">
          <button
            class="btn btn-primary btn-pill"
            :disabled="isLoading"
            @click="submit"
          >
            <span
              v-if="isLoading"
              class="spinner-border spinner-border-sm me-2"
              role="status"
              aria-hidden="true"
            ></span>
            <i v-else class="bx bx-mail-send me-2"></i>
            <template v-if="step === 'request'">
              {{ isLoading ? 'Đang gửi...' : 'Gửi OTP' }}
            </template>
            <template v-else-if="step === 'verify'">
              {{ isLoading ? 'Đang kiểm tra...' : 'Xác nhận OTP' }}
            </template>
            <template v-else>
              {{ isLoading ? 'Đang xử lý...' : 'Đặt lại mật khẩu' }}
            </template>
          </button>
        </div>

        <transition name="fade">
          <div v-if="message" class="alert alert-success py-2">{{ message }}</div>
        </transition>
        <transition name="fade">
          <div v-if="error" class="alert alert-danger py-2">{{ error }}</div>
        </transition>
      </div>
    </div>
  </section>
</template>

<script>
import api from '../../../services/api'

const FORGOT_ENDPOINT =
  import.meta.env?.VITE_FORGOT_PASSWORD_ENDPOINT || '/dkmn/password/forgot'
const RESET_ENDPOINT =
  import.meta.env?.VITE_RESET_PASSWORD_ENDPOINT || '/dkmn/password/reset'
const VERIFY_ENDPOINT =
  import.meta.env?.VITE_VERIFY_OTP_ENDPOINT || '/dkmn/password/verify-otp'

export default {
  name: 'ForgotPassword',
  data() {
    return {
      email: '',
      isLoading: false,
      message: '',
      error: '',
      step: 'request', // request -> verify -> reset
      otp: '',
      password: '',
      password_confirmation: '',
      showPassword: false,
      showPasswordConfirm: false,
    }
  },
  methods: {
    submit() {
      if (this.step === 'request') return this.requestOtp()
      if (this.step === 'verify') return this.verifyOtp()
      return this.resetPassword()
    },
    /**
     * Gửi yêu cầu đặt lại mật khẩu (Request OTP).
     * 
     * Logic hoạt động:
     * 1. Validate email client-side.
     * 2. Gọi API `POST /dkmn/password/forgot` (hoặc endpoint từ env).
     *    - Backend: `ForgotPasswordController::sendResetLinkEmail`.
     *    - Backend kiểm tra email có tồn tại không.
     *    - Nếu có, sinh mã OTP/Token, lưu vào DB/Cache, và gửi email cho user.
     * 3. Xử lý kết quả:
     *    - Thành công: Chuyển sang bước nhập OTP (`step = 'verify'`).
     *    - Thất bại: Hiển thị lỗi.
     */
    async requestOtp() {
      if (this.isLoading) return
      this.error = ''
      this.message = ''

      if (!this.email) {
        this.error = 'Vui lòng nhập email.'
        return
      }

      this.isLoading = true
      try {
        const resp = await api.post(FORGOT_ENDPOINT, { email: this.email })
        this.message =
          resp.data?.message ||
          'Nếu email tồn tại, mã OTP đã được gửi.'
        this.step = 'verify'
        this.$toast?.success(this.message)
      } catch (err) {
        this.error =
          err?.response?.data?.message ||
          (err?.response?.data?.errors && Object.values(err.response.data.errors)[0]?.[0]) ||
          'Không thể gửi yêu cầu. Vui lòng thử lại.'
        this.$toast?.error?.(this.error)
      } finally {
        this.isLoading = false
      }
    },
    /**
     * Xác thực mã OTP người dùng nhập.
     * 
     * Logic hoạt động:
     * 1. Validate OTP client-side.
     * 2. Gọi API `POST /dkmn/password/verify-otp`.
     *    - Backend: `ForgotPasswordController::verifyOtp`.
     *    - Backend kiểm tra OTP có khớp với email và còn hạn không.
     * 3. Xử lý kết quả:
     *    - Thành công: Chuyển sang bước nhập mật khẩu mới (`step = 'reset'`).
     *    - Thất bại: Báo lỗi OTP sai hoặc hết hạn.
     */
    async verifyOtp() {
      if (this.isLoading) return
      this.error = ''
      this.message = ''

      if (!this.otp) {
        this.error = 'Vui lòng nhập OTP.'
        return
      }

      this.isLoading = true
      try {
        const resp = await api.post(VERIFY_ENDPOINT, { email: this.email, otp: this.otp })
        this.message = resp.data?.message || 'OTP hợp lệ, hãy nhập mật khẩu mới.'
        this.step = 'reset'
        this.$toast?.success(this.message)
      } catch (err) {
        this.error =
          err?.response?.data?.message ||
          (err?.response?.data?.errors && Object.values(err.response.data.errors)[0]?.[0]) ||
          'OTP không hợp lệ hoặc đã hết hạn.'
        this.$toast?.error?.(this.error)
      } finally {
        this.isLoading = false
      }
    },
    /**
     * Đặt lại mật khẩu mới.
     * 
     * Logic hoạt động:
     * 1. Validate mật khẩu và xác nhận mật khẩu client-side.
     * 2. Gọi API `POST /dkmn/password/reset`.
     *    - Backend: `ResetPasswordController::reset`.
     *    - Backend verify lại OTP/Token lần cuối (để đảm bảo bảo mật).
     *    - Hash mật khẩu mới và cập nhật vào DB.
     *    - Xóa OTP/Token đã sử dụng.
     * 3. Xử lý kết quả:
     *    - Thành công: Chuyển hướng về trang đăng nhập.
     *    - Thất bại: Báo lỗi.
     */
    async resetPassword() {
      if (this.isLoading) return
      this.error = ''
      this.message = ''

      if (!this.otp || !this.password || !this.password_confirmation) {
        this.error = 'Vui lòng nhập đầy đủ OTP và mật khẩu mới.'
        return
      }

      if (this.password !== this.password_confirmation) {
        this.error = 'Mật khẩu xác nhận không khớp.'
        return
      }

      this.isLoading = true
      try {
        const resp = await api.post(RESET_ENDPOINT, {
          email: this.email,
          otp: this.otp,
          password: this.password,
          password_confirmation: this.password_confirmation,
        })
        this.message = resp.data?.message || 'Đặt lại mật khẩu thành công.'
        this.$toast?.success(this.message)
        this.$router.push('/client-login')
      } catch (err) {
        this.error =
          err?.response?.data?.message ||
          (err?.response?.data?.errors && Object.values(err.response.data.errors)[0]?.[0]) ||
          'Không thể đặt lại mật khẩu. Vui lòng thử lại.'
        this.$toast?.error?.(this.error)
      } finally {
        this.isLoading = false
      }
    },
  },
}
</script>

<style scoped>
.reset-wrapper {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #eef2ff 0%, #f8fbff 100%);
  padding: 2rem 1rem;
}

.reset-card {
  max-width: 540px;
  width: 100%;
  background: #fff;
  border-radius: 18px;
  box-shadow: 0 20px 60px rgba(18, 38, 63, 0.1);
  overflow: hidden;
  border: 1px solid #e9ecef;
}

.reset-header {
  display: flex;
  gap: 1rem;
  align-items: center;
  padding: 1.5rem 1.75rem 1rem;
  border-bottom: 1px solid #f1f3f5;
}

.reset-icon {
  width: 52px;
  height: 52px;
  border-radius: 12px;
  background: linear-gradient(135deg, #1d4ed8, #2563eb);
  color: #fff;
  display: grid;
  place-items: center;
  font-size: 1.5rem;
}

.reset-title {
  margin: 0;
  font-size: 1.4rem;
  font-weight: 800;
  color: #1f2937;
  text-transform: uppercase;
}

.reset-title span {
  color: #2563eb;
}

.reset-sub {
  margin: 0.15rem 0 0.25rem;
  color: #6b7280;
  font-size: 0.95rem;
}

.reset-link {
  margin: 0;
  font-size: 0.9rem;
  color: #4b5563;
}

.reset-link a {
  color: #2563eb;
  font-weight: 600;
  text-decoration: none;
}

.reset-body {
  padding: 1.5rem 1.75rem 1.75rem;
}

.form-label {
  font-weight: 600;
  color: #1f2937;
}

.input-group,
.form-control {
  border-radius: 12px;
}

.form-control {
  padding: 0.75rem 0.85rem;
  border: 1.5px solid #e5e7eb;
  transition: all 0.2s ease;
}

.form-control:focus {
  border-color: #2563eb;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
}

.password-toggle .toggle-visibility {
  border: 1.5px solid #e5e7eb;
  border-left: none;
  background: #f8fafc;
  color: #4b5563;
}

.password-toggle .toggle-visibility:hover {
  color: #2563eb;
}

.btn {
  border-radius: 12px;
  padding: 0.85rem 1rem;
  font-weight: 700;
  letter-spacing: 0.01em;
  box-shadow: 0 8px 20px rgba(37, 99, 235, 0.25);
}

.alert {
  border-radius: 12px;
  border: none;
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  transform: translateY(-4px);
}

input::placeholder {
  color: #adb5bd;
}

@media (max-width: 576px) {
  .reset-card {
    border-radius: 14px;
  }
}
</style>

