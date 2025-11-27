<!-- ClientLogin.vue -->
<template>
  <div class="section-authentication-signin d-flex align-items-center justify-content-center my-5 my-lg-0">
    <div class="container-fluid">
      <div class="row row-cols-1 row-cols-lg-2 row-cols-xl-3">
        <div class="col mx-auto">
          <div class="card radius-10">
            <div class="card-body">
              <div class="border p-4 rounded">
                <div class="text-center">
                  <h3 class="text-uppercase">
                    Đăng Nhập <span class="text-primary fw-bold">DKMN</span>
                  </h3>
                  <p>
                    Bạn chưa có tài khoản?
                    <router-link to="/client-register">Đăng ký ngay</router-link>
                  </p>
                </div>

                <div class="form-body">
                  <div class="row g-3">
                    <div class="col-12">
                      <label class="form-label">Email</label>
                      <div class="input-group">
                        <span class="input-group-text bg-transparent">
                          <i class="fa-solid fa-envelope"></i>
                        </span>
                        <input
                          v-model.trim="thong_tin_dang_nhap.email"
                          type="email"
                          class="form-control border-start-0"
                          placeholder="Email"
                          autocomplete="email"
                        />
                      </div>
                    </div>

                    <div class="col-12">
                      <label class="form-label">Mật khẩu</label>
                      <div class="input-group">
                        <span class="input-group-text bg-transparent">
                          <i class="fa-solid fa-lock"></i>
                        </span>
                        <input
                          v-model="thong_tin_dang_nhap.password"
                          @keydown.enter="dangNhap()"
                          type="password"
                          class="form-control border-start-0"
                          placeholder="Mật khẩu"
                          autocomplete="current-password"
                        />
                      </div>
                    </div>

                    <div class="col-md-6"></div>
                    <div class="col-md-6 text-end">
                      <router-link to="/client/quen-mat-khau">Quên mật khẩu</router-link>
                    </div>

                    <div class="col-12">
                      <div class="d-grid">
                        <button
                          @click="dangNhap"
                          class="btn btn-success btn-pill"
                          :disabled="isLoading"
                        >
                          <span
                            v-if="isLoading"
                            class="spinner-border spinner-border-sm me-2"
                            role="status"
                            aria-hidden="true"
                          ></span>
                          <span v-else class="me-2">
                            <i class="bx bxs-lock-open"></i>
                          </span>
                          {{ isLoading ? 'Đang xử lý...' : 'Đăng Nhập' }}
                        </button>
                      </div>
                    </div>

                    <div v-if="error" class="col-12">
                      <div class="alert alert-danger py-2">{{ error }}</div>
                    </div>
                  </div>
                </div>

                <!-- Không còn nút demo nào -->
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import api from '../../../services/api';

const ADMIN_EMAIL = 'admin@dkmn.com';
export default {
    data() {
        return {
            thong_tin_dang_nhap: {
                email: '',
                password: ''
            },
            isLoading: false,
            error: ''
        }
    },
    methods: {
        async dangNhap() {
            if (this.isLoading) return;

            this.error = '';
            if (!this.thong_tin_dang_nhap.email || !this.thong_tin_dang_nhap.password) {
                this.error = 'Vui lòng nhập đầy đủ email và mật khẩu.';
                this.$toast?.error(this.error);
                return;
            }

            this.isLoading = true;
            this.$toast?.info('Đang xử lý đăng nhập...');
            try {
                const response = await api.post(
                    '/nguoi-dung/dang-nhap',
                    this.thong_tin_dang_nhap
                );

                if (response.data?.status) {
                    const payload = response.data || {};
                    const token =
                        payload.token ||
                        payload.data?.token ||
                        payload.access_token ||
                        '';
                    const rawUserInfo =
                        (payload.data && typeof payload.data === 'object' && !Array.isArray(payload.data)
                            ? payload.data
                            : payload.user || payload.nguoi_dung || {}) || {};
                    const userInfo = { ...rawUserInfo };

                    if (!userInfo.email && this.thong_tin_dang_nhap.email) {
                        userInfo.email = this.thong_tin_dang_nhap.email;
                    }

                    if (!userInfo.ho_va_ten && rawUserInfo?.ho_ten) {
                        userInfo.ho_va_ten = rawUserInfo.ho_ten;
                    }

                    if (!userInfo.ho_ten && userInfo.ho_va_ten) {
                        userInfo.ho_ten = userInfo.ho_va_ten;
                    }

                    if (!userInfo.vai_tro && rawUserInfo?.role) {
                        userInfo.vai_tro = rawUserInfo.role;
                    }

                    if (!userInfo.vai_tro && (userInfo.email || '').toLowerCase() === ADMIN_EMAIL) {
                        userInfo.vai_tro = 'quan_tri';
                    }

                    if (userInfo.vai_tro && !userInfo.role) {
                        userInfo.role = userInfo.vai_tro;
                    }

                    this.$toast?.success(payload.message || 'Đăng nhập thành công');

                    if (token) {
                        localStorage.setItem('token', token);
                        localStorage.setItem('key_client', token);
                    }

                    if (Object.keys(userInfo).length > 0) {
                        try {
                            localStorage.setItem('userInfo', JSON.stringify(userInfo));
                        } catch (storageError) {
                            console.warn('Không thể lưu userInfo xuống localStorage', storageError);
                        }
                    }

                    const role = (userInfo.vai_tro || userInfo.role || '').toLowerCase();
                    const email = (userInfo.email || '').toLowerCase();
                    const isAdmin = role === 'quan_tri' || role === 'admin';
                    const isAdminByEmail = email === ADMIN_EMAIL;

                    const redirectQuery =
                        typeof this.$route?.query?.redirect === 'string' &&
                        this.$route.query.redirect.startsWith('/')
                            ? this.$route.query.redirect
                            : '';

                    let targetRoute = redirectQuery || (isAdmin || isAdminByEmail ? '/admin' : '/');

                    const isAdminRoute = (path) =>
                        typeof path === 'string' &&
                        (path.startsWith('/admin') || path.startsWith('/view/'));

                    if (redirectQuery && !(isAdmin || isAdminByEmail) && isAdminRoute(redirectQuery)) {
                        targetRoute = '/';
                    }

                    if (typeof window !== 'undefined') {
                        window.dispatchEvent(new CustomEvent('dkmn:auth-changed', { detail: { isLoggedIn: true } }));
                    }

                    this.$router.push(targetRoute).catch(() => {});
                } else {
                    const message = response.data?.message || 'Đăng nhập thất bại. Vui lòng thử lại.';
                    this.error = message;
                    this.$toast?.error(message);
                }
            } catch (error) {
                const serverMessage =
                    error.response?.data?.message ||
                    Object.values(error.response?.data?.errors || {})?.[0]?.[0] ||
                    error.message;
                const message = serverMessage || 'Không thể kết nối tới máy chủ. Vui lòng thử lại.';
                this.error = message;
                this.$toast?.error(message);
            } finally {
                this.isLoading = false;
            }
        }
    },
}
</script>
<style scoped>
</style>

