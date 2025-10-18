<template>
    <div class="profile-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                   

                    <!-- Profile Content -->
                    <div class="profile-content">
                        <div class="profile-card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="bx bx-user-circle"></i>
                                    Thông tin cá nhân
                                </h3>
                                <p class="card-subtitle">Cập nhật thông tin hồ sơ của bạn</p>
                            </div>

                            <div class="card-body">
                                <form @submit.prevent="capNhatHoSo" class="profile-form">
                                    <div class="form-grid">
                                        <div class="form-group">
                                            <label class="form-label">
                                                <i class="bx bx-user"></i>
                                                Họ và tên
                                            </label>
                                            <input 
                                                v-model="profile.ho_va_ten" 
                                                type="text" 
                                                class="form-control" 
                                                placeholder="Nhập họ và tên"
                                                required
                                            >
                                        </div>

                                        <div class="form-group">
                                            <label class="form-label">
                                                <i class="bx bx-envelope"></i>
                                                Email
                                            </label>
                                            <input 
                                                v-model="profile.email" 
                                                type="email" 
                                                class="form-control" 
                                                placeholder="Nhập email"
                                                required
                                            >
                                        </div>

                                        <div class="form-group">
                                            <label class="form-label">
                                                <i class="bx bx-phone"></i>
                                                Số điện thoại
                                            </label>
                                            <input 
                                                v-model="profile.so_dien_thoai" 
                                                type="tel" 
                                                class="form-control" 
                                                placeholder="Nhập số điện thoại"
                                            >
                                        </div>

                                        <div class="form-group">
                                            <label class="form-label">
                                                <i class="bx bx-calendar"></i>
                                                Ngày sinh
                                            </label>
                                            <input 
                                                v-model="profile.ngay_sinh" 
                                                type="date" 
                                                class="form-control" 
                                                min="1900-01-01" 
                                                max="2025-12-31"
                                            >
                                        </div>
                                    </div>

                                    <!-- Alert Messages -->
                                    <div v-if="error" class="alert alert-danger">
                                        <i class="bx bx-error-circle"></i>
                                        {{ error }}
                                    </div>
                                    <div v-if="message" class="alert alert-success">
                                        <i class="bx bx-check-circle"></i>
                                        {{ message }}
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="form-actions">
                                        <button 
                                            @click="resetForm" 
                                            type="button" 
                                            class="btn btn-outline-secondary"
                                        >
                                            <i class="bx bx-reset"></i>
                                            Đặt lại
                                        </button>
                                        <button 
                                            @click="capNhatHoSo" 
                                            :disabled="loading" 
                                            type="submit" 
                                            class="btn btn-primary"
                                        >
                                            <span v-if="!loading">
                                                <i class="bx bx-save"></i>
                                                Lưu thay đổi
                                            </span>
                                            <span v-else>
                                                <i class="bx bx-loader-alt bx-spin"></i>
                                                Đang lưu...
                                            </span>
                                        </button>
                                    </div>
                                </form>
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
                ngay_sinh: '',
                avatar: ''
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
                    this.profile.avatar = info.avatar || ''
                    this.original = { ...this.profile }
                }
            } catch (e) {
                // noop
            }
        },
        formatDate(dateString) {
            if (!dateString) return ''
            const date = new Date(dateString)
            return date.toLocaleDateString('vi-VN')
        },
        editAvatar() {
            // Placeholder for avatar editing functionality
            alert('Tính năng chỉnh sửa avatar sẽ được phát triển!')
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
/* 🎨 Profile Container */
.profile-container {
    min-height: 100vh;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 2rem 0;
}

/* 🖼️ Profile Header */
.profile-header {
    margin-bottom: 2rem;
}

.profile-cover {
    position: relative;
    height: 200px;
    background: linear-gradient(135deg, #0056d2 0%, #007bff 100%);
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 20px 40px rgba(0, 86, 210, 0.3);
}

.cover-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, rgba(0, 0, 0, 0.1), rgba(255, 255, 255, 0.1));
}

.profile-avatar-section {
    position: absolute;
    bottom: -50px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    align-items: center;
    gap: 2rem;
    background: rgba(255, 255, 255, 0.95);
    padding: 1.5rem 2rem;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    backdrop-filter: blur(10px);
}

.profile-avatar {
    position: relative;
    width: 120px;
    height: 120px;
    border-radius: 50%;
    overflow: hidden;
    border: 4px solid #fff;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.avatar-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar-placeholder {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #0056d2, #007bff);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 3rem;
}

.avatar-edit-btn {
    position: absolute;
    bottom: 5px;
    right: 5px;
    width: 35px;
    height: 35px;
    background: #0056d2;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(0, 86, 210, 0.4);
}

.avatar-edit-btn:hover {
    background: #003d99;
    transform: scale(1.1);
}

.profile-info {
    flex: 1;
}

.profile-name {
    font-size: 2rem;
    font-weight: 700;
    color: #333;
    margin: 0 0 0.5rem 0;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.profile-email {
    font-size: 1.1rem;
    color: #666;
    margin: 0 0 1rem 0;
}

.profile-stats {
    display: flex;
    gap: 2rem;
    flex-wrap: wrap;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #666;
    font-size: 0.95rem;
}

.stat-item i {
    color: #0056d2;
    font-size: 1.2rem;
}

/* 📋 Profile Content */
.profile-content {
    margin-top: 4rem;
}

.profile-card {
    background: #fff;
    border-radius: 20px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.card-header {
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    padding: 2rem;
    border-bottom: 1px solid #e9ecef;
}

.card-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #333;
    margin: 0 0 0.5rem 0;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.card-title i {
    color: #0056d2;
    font-size: 1.8rem;
}

.card-subtitle {
    color: #666;
    margin: 0;
    font-size: 1rem;
}

.card-body {
    padding: 2rem;
}

/* 📝 Form Styling */
.profile-form {
    max-width: 100%;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-label {
    font-weight: 600;
    color: #333;
    margin-bottom: 0.75rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.95rem;
}

.form-label i {
    color: #0056d2;
    font-size: 1.1rem;
}

.form-control {
    padding: 0.875rem 1rem;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    font-size: 1rem;
    transition: all 0.3s ease;
    background: #f8f9fa;
}

.form-control:focus {
    border-color: #0056d2;
    box-shadow: 0 0 0 0.2rem rgba(0, 86, 210, 0.25);
    background: #fff;
    outline: none;
}

.form-control:hover {
    border-color: #0056d2;
    background: #fff;
}

/* 🚨 Alert Messages */
.alert {
    padding: 1rem 1.25rem;
    border-radius: 12px;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-weight: 500;
    border: none;
}

.alert-danger {
    background: linear-gradient(135deg, #ff6b6b, #ee5a52);
    color: white;
}

.alert-success {
    background: linear-gradient(135deg, #51cf66, #40c057);
    color: white;
}

.alert i {
    font-size: 1.2rem;
}

/* 🔘 Action Buttons */
.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    flex-wrap: wrap;
}

.btn {
    padding: 0.875rem 2rem;
    border-radius: 12px;
    font-weight: 600;
    font-size: 1rem;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    border: 2px solid transparent;
    cursor: pointer;
    text-decoration: none;
}

.btn-outline-secondary {
    background: transparent;
    border-color: #6c757d;
    color: #6c757d;
}

.btn-outline-secondary:hover {
    background: #6c757d;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(108, 117, 125, 0.3);
}

.btn-primary {
    background: linear-gradient(135deg, #0056d2, #007bff);
    border-color: #0056d2;
    color: white;
    box-shadow: 0 4px 15px rgba(0, 86, 210, 0.3);
}

.btn-primary:hover:not(:disabled) {
    background: linear-gradient(135deg, #003d99, #0056b3);
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 86, 210, 0.4);
}

.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none !important;
}

/* 📱 Responsive Design */
@media (max-width: 768px) {
    .profile-container {
        padding: 1rem 0;
    }
    
    .profile-cover {
        height: 150px;
        border-radius: 15px;
    }
    
    .profile-avatar-section {
        flex-direction: column;
        text-align: center;
        gap: 1rem;
        padding: 1rem;
        bottom: -40px;
    }
    
    .profile-avatar {
        width: 80px;
        height: 80px;
    }
    
    .avatar-placeholder {
        font-size: 2rem;
    }
    
    .profile-name {
        font-size: 1.5rem;
    }
    
    .profile-stats {
        justify-content: center;
        gap: 1rem;
    }
    
    .form-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .card-header,
    .card-body {
        padding: 1.5rem;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .btn {
        width: 100%;
        justify-content: center;
    }
}

@media (max-width: 480px) {
    .profile-avatar-section {
        left: 1rem;
        right: 1rem;
        transform: none;
        bottom: -30px;
    }
    
    .profile-stats {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .stat-item {
        justify-content: center;
    }
}

/* ✨ Animations */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.profile-card {
    animation: fadeInUp 0.6s ease-out;
}

.profile-avatar-section {
    animation: fadeInUp 0.8s ease-out;
}

/* 🎯 Loading Animation */
.bx-spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>


