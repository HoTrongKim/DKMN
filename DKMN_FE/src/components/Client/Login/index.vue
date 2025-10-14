<template>
    <div class="section-authentication-signin d-flex align-items-center justify-content-center my-5 my-lg-0">
        <div class="container-fluid">
            <div class="row row-cols-1 row-cols-lg-2 row-cols-xl-3">
                <div class="col mx-auto">
                    <div class="card radius-10">
                        <div class="card-body">
                            <div class="border p-4 rounded">
                                <div class="text-center">
                                    <h3 class="text-uppercase">Đăng Nhập <span
                                            class="text-primary fw-bold">DKMN</span></h3>
                                    <p>Bạn chưa có tài khoản?
                                        <router-link to="/client-register">
                                            Đăng ký ngay
                                        </router-link>
                                    </p>
                                </div>
                                <div class="d-grid">
                                    <!-- <a class="btn my-3 shadow-sm btn-white" href="javascript:;"> <span
                                            class="d-flex justify-content-center align-items-center">
                                            <img class="me-2" src="../../../assets/images/icons/search.svg" width="16"
                                                alt="Image Description">
                                            <span>Đăng nhập bằng Google</span>
                                        </span>
                                    </a> -->
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
                                                <input v-model="thong_tin_dang_nhap.password" @keydown.enter="dangNhap()" type="password"
                                                    class="form-control border-start-0" placeholder="Mật khẩu">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                        </div>
                                        <div class="col-md-6 text-end">
                                            <router-link to="/client/quen-mat-khau">
                                                Quên mật khẩu
                                            </router-link>
                                        </div>
                                        <div class="col-12">
                                            <div class="d-grid">
                                                <button v-on:click="dangNhap()" class="btn btn-success btn-pill"><i
                                                        class="bx bxs-lock-open"></i>Đăng Nhập</button>
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
import axios from 'axios';
export default {
    data() {
        return {
            thong_tin_dang_nhap: {}
        }
    },
    methods: {
        async dangNhap() {
            try {
                // Kiểm tra dữ liệu đầu vào
                if (!this.thong_tin_dang_nhap.email || !this.thong_tin_dang_nhap.password) {
                    alert('Vui lòng nhập đầy đủ email và mật khẩu!');
                    return;
                }

                // Gọi API đăng nhập
                const response = await axios.post('http://localhost:8000/api/auth/login', this.thong_tin_dang_nhap);
                
                if (response.data.status) {
                    // Lưu token và thông tin user vào localStorage
                    localStorage.setItem('token', response.data.token);
                    localStorage.setItem('userInfo', JSON.stringify(response.data.user));
                    
                    // Thông báo thành công
                    alert('Đăng nhập thành công!');
                    
                    // Chuyển hướng đến trang MenuClient
                    this.$router.push('/client-menu');
                } else {
                    alert(response.data.message || 'Đăng nhập thất bại!');
                }
            } catch (error) {
                console.error('Lỗi đăng nhập:', error);
                if (error.response && error.response.data && error.response.data.message) {
                    alert(error.response.data.message);
                } else {
                    alert('Có lỗi xảy ra khi đăng nhập. Vui lòng thử lại!');
                }
            }
        }
    },
}
</script>
<style></style>