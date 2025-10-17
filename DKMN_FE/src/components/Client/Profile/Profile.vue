<template>
    <div class="d-flex align-items-center justify-content-center my-5 my-lg-0"
        style="background-position: center; min-height: 100vh;">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="my-4 text-center"></div>
                    <div class="card d-flex">
                        <div class="card-body flex-full">
                            <div class="border p-4 rounded">
                                <div class="text-center">
                                    <h3 class="text-uppercase ">Thông tin cá nhân <b class="text-primary">DKMN</b>
                                    </h3>
                                    <p class="text-muted">Cập nhật thông tin hồ sơ của bạn</p>
                                </div>
                                <div class="form-body">
                                    <div class="row g-3">
                                        <div class="col-sm-6">
                                            <label class="form-label">Họ và tên</label>
                                            <input v-model="profile.ho_va_ten" type="text" class="form-control" placeholder="Nhập họ và tên">
                                        </div>
                                        <div class="col-sm-6">
                                            <label class="form-label">Email</label>
                                            <input v-model="profile.email" type="email" class="form-control" placeholder="Nhập email">
                                        </div>
                                        <div class="col-sm-6">
                                            <label class="form-label">Số điện thoại</label>
                                            <input v-model="profile.so_dien_thoai" type="text" class="form-control" placeholder="Nhập số điện thoại">
                                        </div>
                                        <div class="col-sm-6">
                                            <label class="form-label">Ngày Sinh</label>
                                            <input v-model="profile.ngay_sinh" type="date" class="form-control" min="1900-01-01" max="2025-12-31">
                                        </div>

                                        <div class="col-12">
                                            <div v-if="error" class="alert alert-danger py-2">{{ error }}</div>
                                            <div v-if="message" class="alert alert-success py-2">{{ message }}</div>
                                        </div>

                                        <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                                            <button @click="resetForm" type="button" class="btn btn-outline-secondary">
                                                <i class="bx bx-reset"></i>
                                                Đặt lại
                                            </button>
                                            <button @click="capNhatHoSo" :disabled="loading" type="button" class="btn btn-success text-uppercase">
                                                <span v-if="!loading"><i class="bx bx-save"></i> Lưu thay đổi</span>
                                                <span v-else>Đang lưu...</span>
                                            </button>
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
import axios from 'axios'

export default {
    data() {
        return {
            profile: {
                ho_va_ten: '',
                email: '',
                so_dien_thoai: '',
                ngay_sinh: ''
            },
            original: {},
            loading: false,
            error: '',
            message: '',
            DEV_FALLBACK: true
        }
    },
    mounted() {
        this.loadFromLocalStorage()
    },
    methods: {
        loadFromLocalStorage() {
            try {
                const raw = localStorage.getItem('userInfo');
                if (raw) {
                    const info = JSON.parse(raw);
                    // Map các tên trường phổ biến từ đăng nhập/đăng ký
                    this.profile.ho_va_ten = info.ho_va_ten || info.name || ''
                    this.profile.email = info.email || ''
                    this.profile.so_dien_thoai = info.so_dien_thoai || info.phone || ''
                    this.profile.ngay_sinh = info.ngay_sinh || ''
                    this.original = { ...this.profile }
                }
            } catch (e) {
                // noop
            }
        },
        resetForm() {
            this.profile = { ...this.original }
            this.error = ''
            this.message = ''
        },
        async capNhatHoSo() {
            this.error = ''
            this.message = ''

            if (!this.profile.ho_va_ten || !this.profile.email) {
                this.error = 'Vui lòng nhập đầy đủ họ tên và email.'
                return
            }

            this.loading = true
            try {
                const token = localStorage.getItem('token') || ''

                // Ví dụ endpoint cập nhật hồ sơ (điều chỉnh theo backend của bạn)
                const resp = await axios.put(
                    'http://localhost:8000/api/auth/profile',
                    {
                        ho_va_ten: this.profile.ho_va_ten,
                        email: this.profile.email,
                        so_dien_thoai: this.profile.so_dien_thoai,
                        ngay_sinh: this.profile.ngay_sinh
                    },
                    {
                        headers: token ? { Authorization: `Bearer ${token}` } : {},
                        timeout: 5000
                    }
                )

                if (resp.data) {
                    // Đồng bộ localStorage theo response (nếu backend trả user)
                    const updated = resp.data.user || {
                        ho_va_ten: this.profile.ho_va_ten,
                        name: this.profile.ho_va_ten,
                        email: this.profile.email,
                        so_dien_thoai: this.profile.so_dien_thoai,
                        ngay_sinh: this.profile.ngay_sinh
                    }
                    localStorage.setItem('userInfo', JSON.stringify(updated))
                    this.original = { ...this.profile }
                    this.message = resp.data.message || 'Cập nhật hồ sơ thành công!'
                } else {
                    this.error = 'Không nhận được phản hồi từ máy chủ.'
                }
            } catch (err) {
                // Dev fallback: nếu không có backend, vẫn lưu localStorage để tiếp tục phát triển UI
                if (this.DEV_FALLBACK) {
                    localStorage.setItem('userInfo', JSON.stringify({
                        ho_va_ten: this.profile.ho_va_ten,
                        name: this.profile.ho_va_ten,
                        email: this.profile.email,
                        so_dien_thoai: this.profile.so_dien_thoai,
                        ngay_sinh: this.profile.ngay_sinh
                    }))
                    this.original = { ...this.profile }
                    this.message = 'Đã lưu tạm vào trình duyệt (dev fallback).'
                } else {
                    this.error = (err && err.response && err.response.data && err.response.data.message)
                        || 'Có lỗi xảy ra khi cập nhật hồ sơ.'
                }
            } finally {
                this.loading = false
            }
        }
    }
}
</script>

<style scoped>
/* Bạn có thể bổ sung style nếu cần */
</style>


