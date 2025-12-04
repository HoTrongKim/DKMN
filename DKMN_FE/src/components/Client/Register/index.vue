<template>
    <div class="d-flex align-items-center justify-content-center my-5 my-lg-0"
        style="background-position: center; height: 100vh;">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="my-4 text-center"></div>
                    <div class="card d-flex">
                        <div class="card-body flex-full">
                            <div class="border p-4 rounded">
                                <div class="text-center">
                                    <h3 class="text-uppercase ">Đăng ký tài khoản <b class="text-primary">DKMN</b>
                                    </h3>
                                    <p>Bạn đã có tài khoản?
                                        <router-link to="/client-login">
                                            Đăng nhập tại đây
                                        </router-link>
                                    </p>
                                </div>
                                <div class="form-body">
                                    <div class="row g-3">
                                        <div class="col-sm-6">
                                            <label class="form-label">Họ và tên</label>
                                            <input v-model="user.ho_va_ten" type="text" class="form-control">
                                        </div>
                                        <div class="col-sm-6">
                                            <label for="inputEmailAddress" class="form-label">Email</label>
                                            <input v-model="user.email" type="email" class="form-control">
                                        </div>
                                        <div class="col-sm-6">
                                            <label class="form-label">Số điện thoại</label>
                                            <input v-model="user.so_dien_thoai" type="text" class="form-control">
                                        </div>
                                        <!-- <div class="col-sm-4">
                                            <label class="form-label">Số CCCD</label>
                                            <input v-model="user.cccd" type="text" class="form-control">
                                        </div> -->
                                        <div class="col-sm-6">
                                            <label class="form-label">Ngày Sinh</label>
                                            <input v-model="user.ngay_sinh" type="date" class="form-control" min="1900-01-01" :max="maxDate">
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label">Mật khẩu</label>
                                            <div class="input-group">
                                                <input
                                                    v-model="user.password"
                                                    :type="showPassword ? 'text' : 'password'"
                                                    class="form-control border-end-0"
                                                >
                                                <button
                                                    type="button"
                                                    class="input-group-text bg-transparent"
                                                    @click="showPassword = !showPassword"
                                                    :aria-label="showPassword ? 'Ẩn mật khẩu' : 'Hiện mật khẩu'"
                                                >
                                                    <i :class="showPassword ? 'bx bx-show' : 'bx bx-hide'"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label">Nhập Lại Mật khẩu</label>
                                            <div class="input-group">
                                                <input
                                                    v-model="user.re_password"
                                                    :type="showRePassword ? 'text' : 'password'"
                                                    class="form-control border-end-0"
                                                >
                                                <button
                                                    type="button"
                                                    class="input-group-text bg-transparent"
                                                    @click="showRePassword = !showRePassword"
                                                    :aria-label="showRePassword ? 'Ẩn mật khẩu' : 'Hiện mật khẩu'"
                                                >
                                                    <i :class="showRePassword ? 'bx bx-show' : 'bx bx-hide'"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox"
                                                    id="flexSwitchCheckChecked">
                                                <label class="form-check-label" for="flexSwitchCheckChecked">Bằng việc
                                                    đăng ký tài khoản, tôi đồng ý với Điều khoản dịch vụ &amp; Chính
                                                    sách bảo mật của <b>DKMN</b>.</label>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="d-grid">
                                                <button @click="dangKyTaiKhoan()" type="submit" class="btn btn-success text-uppercase"><i
                                                        class="bx bx-user"></i>
                                                    Đăng Ký</button>
                                            </div>
                                        </div>
                                    </div>
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
import api from '../../../services/api';

export default {
    data() {
        return {
            user: {
                ho_va_ten: '',
                email: '',
                so_dien_thoai: '',
                ngay_sinh: '',
                password: '',
                re_password: ''
            },
            isLoading: false,
            error: '',
            message: '',
            showPassword: false,
            showRePassword: false
        }
    },
    computed: {
        maxDate() {
            return new Date().toISOString().slice(0, 10)
        }
    },
    methods: {
        isFutureDate(dateStr) {
            if (!dateStr) return false;
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            const d = new Date(dateStr);
            d.setHours(0, 0, 0, 0);
            return d > today;
        },
        /**
         * Xử lý đăng ký tài khoản mới.
         * 
         * Logic hoạt động:
         * 1. Validate form client-side:
         *    - Kiểm tra các trường bắt buộc (Họ tên, Email, SĐT, Mật khẩu).
         *    - Kiểm tra định dạng số điện thoại (10 số).
         *    - Kiểm tra mật khẩu nhập lại có khớp không.
         *    - Kiểm tra ngày sinh không được ở tương lai.
         * 2. Chuẩn bị payload gửi lên server:
         *    - Map các trường dữ liệu vào payload (ho_ten, email, so_dien_thoai, ngay_sinh, password).
         * 3. Gọi API `POST /nguoi-dung/dang-ky`.
         *    - Backend: `NguoiDungController::dangKy`.
         *    - Backend sẽ validate tính duy nhất của email và số điện thoại.
         *    - Backend hash mật khẩu và tạo record user mới.
         * 4. Xử lý kết quả:
         *    - Thành công: Hiển thị thông báo và chuyển hướng sang trang đăng nhập.
         *    - Thất bại: Hiển thị lỗi từ server (ví dụ: email đã tồn tại).
         */
        async dangKyTaiKhoan() {
            if (this.isLoading) return;

            this.error = '';
            this.message = '';

            const phone = (this.user.so_dien_thoai || '').trim();

            if (!this.user.ho_va_ten || !this.user.email || !phone || !this.user.password || !this.user.re_password) {
                this.error = 'Vui lòng nhập đầy đủ thông tin bắt buộc.';
                this.$toast?.error(this.error);
                return;
            }

            if (!/^\d{10}$/.test(phone)) {
                this.error = 'Số điện thoại phải đủ 10 chữ số.';
                this.$toast?.error(this.error);
                return;
            }

            if (this.user.password !== this.user.re_password) {
                this.error = 'Mật khẩu nhập lại không khớp.';
                this.$toast?.error(this.error);
                return;
            }

            if (this.isFutureDate(this.user.ngay_sinh)) {
                this.error = 'Ngày sinh không được vượt quá ngày hiện tại.';
                this.$toast?.error(this.error);
                return;
            }

            this.isLoading = true;
            this.$toast?.info('Đang xử lý đăng ký...');
            
            try {
                const payload = {
                    ho_ten: this.user.ho_va_ten,
                    ho_va_ten: this.user.ho_va_ten,
                    email: this.user.email,
                    so_dien_thoai: phone,
                    ngay_sinh: this.user.ngay_sinh,
                    password: this.user.password,
                    mat_khau: this.user.password
                };

                const response = await api.post('/nguoi-dung/dang-ky', payload);
                if (response.data?.status) {
                    this.message = response.data?.message || 'Đăng ký thành công. Vui lòng đăng nhập.';
                    this.$toast?.success(this.message);
                    this.$router.push('/client-login');
                } else {
                    const msg = response.data?.message || 'Đăng ký thất bại. Vui lòng thử lại.';
                    this.error = msg;
                    this.$toast?.error(msg);
                }
            } catch (error) {
                const serverMessage =
                    error.response?.data?.message ||
                    Object.values(error.response?.data?.errors || {})?.[0]?.[0] ||
                    error.message;
                this.error = serverMessage || 'Không thể kết nối tới máy chủ. Vui lòng thử lại.';
                this.$toast?.error(this.error);
            } finally {
                this.isLoading = false;
            }
        }
    },
}
</script>
<style></style>

