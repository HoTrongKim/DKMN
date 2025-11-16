<template>
  <header class="topbar">
    <div class="left">
      <button class="menu-toggle" @click="$emit('toggle-sidebar')" aria-label="Thu gọn/mở rộng sidebar">
        <i class="fas fa-bars"></i>
      </button>
      <div class="brand" @click="$router.push('/view/home')">
        <img class="logo" src="/src/assets/image/logo.png" alt="DKMN" />
        <h2>DKMN Admin</h2>
      </div>
    </div>
    <div class="right">
      <div class="search-box">
        <i class="fas fa-search"></i>
        <input type="text" placeholder="Tìm kiếm nhanh..." />
      </div>
      <div class="topbar-icons">
        <i class="fas fa-bell"></i>
        <i class="fas fa-cog"></i>
      </div>
      <div class="avatar" ref="avatarWrapper">
        <img :src="avatarUrl" alt="admin avatar" @click.stop="toggleMenu" />
        <transition name="fade">
          <div v-if="showMenu" class="avatar-menu">
            <div class="avatar-header">
              <strong>{{ adminName }}</strong>
              <span>{{ adminEmail }}</span>
            </div>
            <button class="avatar-menu-item" @click="goToProfile">
              <i class="fas fa-user-circle"></i>
              <span>Thông tin cá nhân</span>
            </button>
            <button class="avatar-menu-item" @click="goToSettings">
              <i class="fas fa-shield-alt"></i>
              <span>Bảo mật & mật khẩu</span>
            </button>
            <button class="avatar-menu-item logout" @click="handleLogout">
              <i class="fas fa-sign-out-alt"></i>
              <span>Đăng xuất</span>
            </button>
          </div>
        </transition>
      </div>
    </div>
  </header>
  
</template>

<script>
export default {
  name: 'TopAdmin',
  data() {
    return {
      showMenu: false,
      adminName: 'Quản trị DKMN',
      adminEmail: 'admin@dkmn.com',
      avatarUrl: 'https://cdn-icons-png.flaticon.com/512/149/149071.png',
    }
  },
  mounted() {
    document.addEventListener('click', this.handleClickOutside)
    window.addEventListener('dkmn:auth-changed', this.loadAdminInfo)
    this.loadAdminInfo()
  },
  beforeUnmount() {
    document.removeEventListener('click', this.handleClickOutside)
    window.removeEventListener('dkmn:auth-changed', this.loadAdminInfo)
  },
  methods: {
    toggleMenu() {
      this.showMenu = !this.showMenu
    },
    handleClickOutside(event) {
      if (!this.$refs.avatarWrapper) return
      if (!this.$refs.avatarWrapper.contains(event.target)) {
        this.showMenu = false
      }
    },
    goToProfile() {
      this.showMenu = false
      this.$router.push('/admin/profile')
    },
    goToSettings() {
      this.showMenu = false
      this.$router.push('/admin/account-security')
    },
    handleLogout() {
      this.showMenu = false
      this.$emit('logout')
    },
    loadAdminInfo() {
      try {
        const raw = localStorage.getItem('userInfo')
        if (!raw) return
        const info = JSON.parse(raw)
        if (info?.ho_ten || info?.name || info?.ho_va_ten) {
          this.adminName = info.ho_ten || info.ho_va_ten || info.name
        }
        if (info?.email) {
          this.adminEmail = info.email
        }
        if (info?.avatar) {
          this.avatarUrl = info.avatar
        }
      } catch (error) {
        console.warn('Cannot load admin info', error)
      }
    },
  },
}
</script>

<style scoped>
.topbar {
  background-color: #0b3b6e; color: white;
  padding: 12px 24px; display: flex; justify-content: space-between; align-items: center;
  position: relative; z-index: 10;
}
.left { display: flex; align-items: center; gap: 12px; }
.menu-toggle { background: transparent; border: none; color: #fff; font-size: 20px; cursor: pointer; }
.brand { display: flex; align-items: center; gap: 10px; cursor: pointer; }
.brand .logo { width: 28px; height: 28px; border-radius: 6px; }
.brand h2 { font-size: 1.1rem; margin: 0; }
.topbar .right { display: flex; align-items: center; gap: 18px; }
.search-box { position: relative; }
.search-box input {
  padding: 6px 28px 6px 10px;
  border: 1px solid rgba(255,255,255,.35);
  border-radius: 20px; outline: none; transition: .2s;
  font-size: .9rem; background: rgba(255,255,255,.15); color: #fff;
}
.search-box input::placeholder { color: rgba(255,255,255,.8); }
.search-box i { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); color: #fff; font-size: .9rem; }
.topbar-icons i { font-size: 1.1rem; color: #fff; margin: 0 6px; cursor: pointer; transition: .2s; }
.topbar-icons i:hover { color: #e6f0ff; }
.avatar { position: relative; }
.avatar img { width: 38px; height: 38px; border-radius: 50%; border: 2px solid #e6f0ff; cursor: pointer; transition: .2s; box-shadow: 0 6px 16px rgba(15, 23, 42, 0.2); }
.avatar img:hover { transform: scale(1.05); }
.avatar-menu {
  position: absolute;
  top: 48px;
  right: 0;
  width: 240px;
  background: #fff;
  border-radius: 18px;
  box-shadow: 0 18px 40px rgba(15, 23, 42, 0.18);
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 8px;
  z-index: 20;
}
.avatar-header {
  border-bottom: 1px solid #e2e8f0;
  padding-bottom: 8px;
  margin-bottom: 8px;
}
.avatar-header strong {
  display: block;
  font-size: 0.95rem;
  color: #0f172a;
}
.avatar-header span {
  font-size: 0.8rem;
  color: #64748b;
}
.avatar-menu-item {
  display: flex;
  align-items: center;
  gap: 10px;
  border: none;
  background: transparent;
  font-size: 0.9rem;
  color: #0f172a;
  padding: 8px 6px;
  border-radius: 12px;
  cursor: pointer;
  transition: background 0.2s;
}
.avatar-menu-item i { color: #0ea5e9; }
.avatar-menu-item.logout i { color: #ef4444; }
.avatar-menu-item:hover {
  background: rgba(14, 165, 233, 0.08);
}
.avatar-menu-item.logout:hover {
  background: rgba(239, 68, 68, 0.08);
}
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.15s ease, transform 0.15s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  transform: translateY(-4px);
}
</style>
