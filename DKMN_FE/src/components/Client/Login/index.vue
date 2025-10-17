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
  
                  <div class="d-flex gap-2 justify-content-center mb-3">
                    <!-- Nút điền nhanh và đăng nhập demo -->
                    <button class="btn btn-outline-secondary btn-sm" @click="fillDemo">Điền tài khoản demo</button>
                    <button class="btn btn-outline-primary btn-sm" @click="loginDemo">Đăng nhập demo</button>
                  </div>
  
                  <div class="login-separater text-center mb-4">
                    <span>OR</span>
                    <hr>
                  </div>
  
                  <div class="form-body">
                    <div class="row g-3">
                      <div class="col-12">
                        <label class="form-label">Email</label>
                        <div class="input-group">
                          <span class="input-group-text bg-transparent">
                            <i class="fa-solid fa-envelope"></i>
                          </span>
                          <input v-model="thong_tin_dang_nhap.email" type="email"
                                 class="form-control border-start-0" placeholder="Email">
                        </div>
                      </div>
  
                      <div class="col-12">
                        <label class="form-label">Mật khẩu</label>
                        <div class="input-group">
                          <span class="input-group-text bg-transparent">
                            <i class="fa-solid fa-lock"></i>
                          </span>
                          <input v-model="thong_tin_dang_nhap.password"
                                 @keydown.enter="dangNhap()" type="password"
                                 class="form-control border-start-0" placeholder="Mật khẩu">
                        </div>
                      </div>
  
                      <div class="col-md-6"></div>
                      <div class="col-md-6 text-end">
                        <router-link to="/client/quen-mat-khau">Quên mật khẩu</router-link>
                      </div>
  
                      <div class="col-12">
                        <div class="d-grid">
                          <button @click="dangNhap" class="btn btn-success btn-pill">
                            <i class="bx bxs-lock-open"></i> Đăng Nhập
                          </button>
                        </div>
                      </div>
  
                      <div v-if="error" class="col-12">
                        <div class="alert alert-danger py-2">{{ error }}</div>
                      </div>
  
                    </div>
                  </div>
  
                  <div class="mt-3 small text-muted">
                    <strong>Ghi chú:</strong> nút <em>Đăng nhập demo</em> sẽ mock login (không gọi API) — dùng để
                    thiết kế & test giao diện khi backend chưa sẵn sàng.
                  </div>
  
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  
  </template>
  
  <script>
  import axios from 'axios';
  
  export default {
    data() {
      return {
        // thông tin nhập từ form
        thong_tin_dang_nhap: {
          email: '',
          password: ''
        },
        // thông tin tài khoản demo mặc định
        demoAccount: {
          email: 'demo@dkmn.test',
          password: 'demo123',
          name: 'Người Dùng Demo',
          id: 999,
          avatar: ''
        },
        error: '' ,
        // bật dev mock: nếu true thì khi gọi dangNhap() thất bại (không có backend),
        // sẽ fallback sang mock (tự động login) — tiện dev
        DEV_FALLBACK: true
      }
    },
    methods: {
      // Autofill form bằng thông tin demo
      fillDemo() {
        this.thong_tin_dang_nhap.email = this.demoAccount.email;
        this.thong_tin_dang_nhap.password = this.demoAccount.password;
        this.error = '';
      },
  
      // Đăng nhập demo (không gọi API) — lưu localStorage và chuyển route
      loginDemo() {
        // Lưu token giả và user info demo vào localStorage
        localStorage.setItem('token', 'FAKE-DEMO-TOKEN');
        localStorage.setItem('userInfo', JSON.stringify({
          id: this.demoAccount.id,
          name: this.demoAccount.name,
          email: this.demoAccount.email,
          avatar: this.demoAccount.avatar
        }));
        // toast / alert nhẹ
        alert('Đăng nhập demo thành công!');
        this.$router.push('/client-menu');
      },
  
      // Hàm gọi API thực tế
      async dangNhap() {
        try {
          this.error = '';
          if (!this.thong_tin_dang_nhap.email || !this.thong_tin_dang_nhap.password) {
            this.error = 'Vui lòng nhập đầy đủ email và mật khẩu!';
            return;
          }
  
          // Gọi API thực tế
          const response = await axios.post('http://localhost:8000/api/auth/login', this.thong_tin_dang_nhap, {
            // timeout ngắn để dev không chờ quá lâu nếu backend ko tồn tại
            timeout: 5000
          });
  
          if (response.data && response.data.status) {
            localStorage.setItem('token', response.data.token);
            localStorage.setItem('userInfo', JSON.stringify(response.data.user));
            alert('Đăng nhập thành công!');
            this.$router.push('/client-menu');
          } else {
            this.error = response.data.message || 'Đăng nhập thất bại!';
          }
  
        } catch (err) {
          console.error('Lỗi đăng nhập:', err);
  
          // Nếu dev fallback bật thì tự login bằng demo để tiếp tục thiết kế
          if (this.DEV_FALLBACK) {
            const proceed = confirm('Không kết nối được tới backend. Bạn có muốn đăng nhập demo để test giao diện không?');
            if (proceed) {
              this.loginDemo();
              return;
            } else {
              this.error = 'Không thể đăng nhập (không có kết nối đến server).';
            }
          } else {
            // Hiển thị thông điệp lỗi chi tiết nếu có
            if (err.response && err.response.data && err.response.data.message) {
              this.error = err.response.data.message;
            } else {
              this.error = 'Có lỗi xảy ra khi đăng nhập. Vui lòng thử lại!';
            }
          }
        }
      }
    }
  }
  </script>
  
  <style scoped>
  /* bạn thêm style nếu cần */
  </style>
  