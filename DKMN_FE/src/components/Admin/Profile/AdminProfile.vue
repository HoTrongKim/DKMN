<template>
  <div class="admin-profile">
    <h2 class="page-title">Thông tin quản trị</h2>
    <div class="profile-card">
      <div class="avatar-wrapper">
        <img :src="profile.avatar || defaultAvatar" alt="Admin avatar" />
        <label class="avatar-upload">
          <i class="fas fa-camera"></i>
          <input type="file" accept="image/*" @change="previewAvatar" />
        </label>
      </div>
      <div class="profile-info">
        <div class="form-group">
          <label>Họ tên</label>
          <input type="text" v-model="profile.name" />
        </div>
        <div class="form-group">
          <label>Email</label>
          <input type="email" v-model="profile.email" disabled />
        </div>
        <div class="form-group">
          <label>Số điện thoại</label>
          <input type="tel" v-model="profile.phone" />
        </div>
        <div class="form-group">
          <label>Vai trò</label>
          <input type="text" v-model="profile.role" disabled />
        </div>
        <button class="btn btn-primary" @click="saveProfile" :disabled="isSaving || isLoading">
          <span v-if="isSaving" class="spinner-border spinner-border-sm me-2"></span>
          <i v-else class="fas fa-save me-1"></i>
          {{ isSaving ? 'Đang lưu...' : 'Lưu thay đổi' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import api from '../../../services/api'

export default {
  name: "AdminProfile",
  data() {
    return {
      profile: {
        name: "",
        email: "",
        phone: "",
        role: "",
        avatar: "",
      },
      defaultAvatar:
        "https://cdn-icons-png.flaticon.com/512/149/149071.png",
      isSaving: false,
      isLoading: false,
    };
  },
  mounted() {
    this.fetchProfile();
  },
  methods: {
    /**
     * Tải thông tin hồ sơ quản trị viên từ Backend.
     * 
     * Logic hoạt động:
     * 1. Gọi API `GET /dkmn/me` để lấy thông tin người dùng hiện tại từ session/token.
     *    - Backend (Laravel): `AuthController::me()` hoặc `UserController::show()`.
     *    - Trả về: Object chứa `ho_ten`, `email`, `so_dien_thoai`, `vai_tro`, ...
     * 2. Map dữ liệu từ response vào `this.profile`.
     *    - Sử dụng fallback value nếu field bị null.
     * 3. Kiểm tra `localStorage` ('userInfo') để lấy avatar đã cache (nếu có),
     *    vì API `/dkmn/me` có thể chưa trả về URL avatar đầy đủ hoặc cần lấy từ client-side cache.
     * 4. Xử lý lỗi: Nếu API lỗi, hiển thị toast error.
     */
    async fetchProfile() {
      this.isLoading = true;
      try {
        const { data } = await api.get('/dkmn/me');
        const info = data?.data || {};
        
        // Map response fields to component state
        this.profile.name = info.ho_ten || "Quản trị DKMN";
        this.profile.email = info.email || "admin@dkmn.com";
        this.profile.phone = info.so_dien_thoai || "";
        this.profile.role = info.vai_tro || info.role || "Quản trị";

        // Check local storage for cached avatar
        const raw = localStorage.getItem('userInfo');
        if (raw) {
          const cached = JSON.parse(raw);
          if (cached.avatar) {
            this.profile.avatar = cached.avatar;
          }
        }
      } catch (error) {
        console.warn('cannot fetch admin profile', error);
        this.$toast?.error?.('Không thể tải thông tin tài khoản.');
      } finally {
        this.isLoading = false;
      }
    },
    /**
     * Xử lý xem trước ảnh đại diện khi người dùng chọn file.
     * 
     * Logic hoạt động:
     * 1. Lắng nghe sự kiện `change` từ input file.
     * 2. Kiểm tra nếu có file được chọn.
     * 3. Sử dụng `FileReader` để đọc file dưới dạng DataURL (base64).
     * 4. Gán kết quả vào `this.profile.avatar` để hiển thị ngay lập tức trên UI (Client-side preview).
     * Lưu ý: Ảnh chưa được upload lên server tại bước này, chỉ upload khi gọi `saveProfile`.
     */
    previewAvatar(event) {
      const file = event.target.files?.[0];
      if (!file) return;
      const reader = new FileReader();
      reader.onload = (e) => {
        this.profile.avatar = e.target.result;
      };
      reader.readAsDataURL(file);
    },
    /**
     * Lưu thông tin hồ sơ đã chỉnh sửa lên Server.
     * 
     * Logic hoạt động:
     * 1. Gọi API `PUT /dkmn/me` với payload gồm `ho_ten`, `so_dien_thoai`.
     *    - Backend: `AuthController::updateProfile` (hoặc tương đương).
     *    - Backend sẽ validate dữ liệu và update vào DB.
     * 2. Cập nhật `localStorage` ('userInfo') để đồng bộ dữ liệu mới (bao gồm cả avatar base64 nếu có).
     *    - Lưu ý: Trong thực tế, nên upload avatar qua API upload riêng (multipart/form-data) để lấy URL ảnh, 
     *      nhưng ở đây đang lưu base64 hoặc URL vào local storage tạm thời.
     * 3. Dispatch event `dkmn:auth-changed` để các component khác (như Navbar/Sidebar) cập nhật UI.
     * 4. Hiển thị thông báo thành công hoặc lỗi.
     */
    async saveProfile() {
      this.isSaving = true;
      try {
        // Call API to update profile info
        await api.put('/dkmn/me', {
          ho_ten: this.profile.name,
          so_dien_thoai: this.profile.phone,
        });

        // Update Local Storage
        const raw = localStorage.getItem('userInfo');
        const info = raw ? JSON.parse(raw) : {};
        info.ho_ten = this.profile.name;
        info.so_dien_thoai = this.profile.phone;
        info.avatar = this.profile.avatar; // Saving avatar (potentially base64) to local storage
        localStorage.setItem('userInfo', JSON.stringify(info));

        // Notify other components
        window.dispatchEvent(new CustomEvent('dkmn:auth-changed', { detail: { isLoggedIn: true } }));
        this.$toast?.success?.('Đã lưu thông tin quản trị.');
      } catch (error) {
        console.warn('cannot save profile', error);
        const message = error.response?.data?.message || 'Không thể lưu thông tin.';
        this.$toast?.error?.(message);
      } finally {
        this.isSaving = false;
      }
    },
  },
};
</script>

<style scoped>
.admin-profile {
  max-width: 900px;
  margin: 0 auto;
}
.page-title {
  font-size: 1.5rem;
  font-weight: 700;
  margin-bottom: 24px;
  color: #0f172a;
}
.profile-card {
  display: grid;
  grid-template-columns: 220px 1fr;
  gap: 24px;
  background: #fff;
  padding: 24px;
  border-radius: 20px;
  box-shadow: 0 20px 40px rgba(15, 23, 42, 0.08);
}
.avatar-wrapper {
  position: relative;
  width: 180px;
  height: 180px;
  margin: 0 auto;
}
.avatar-wrapper img {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  object-fit: cover;
  border: 4px solid #dbeafe;
}
.avatar-upload {
  position: absolute;
  bottom: 10px;
  right: 10px;
  background: #0ea5e9;
  color: #fff;
  width: 38px;
  height: 38px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  box-shadow: 0 8px 15px rgba(14, 165, 233, 0.3);
}
.avatar-upload input {
  display: none;
}
.profile-info .form-group {
  margin-bottom: 16px;
  display: flex;
  flex-direction: column;
}
.profile-info label {
  font-weight: 600;
  margin-bottom: 6px;
  color: #475467;
}
.profile-info input {
  border: 1px solid #d0d5dd;
  border-radius: 12px;
  padding: 10px 12px;
  font-size: 0.95rem;
  transition: border 0.2s;
}
.profile-info input:focus {
  border-color: #0ea5e9;
  outline: none;
  box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.15);
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
@media (max-width: 767px) {
  .profile-card {
    grid-template-columns: 1fr;
  }
  .avatar-wrapper {
    width: 140px;
    height: 140px;
  }
}
</style>
