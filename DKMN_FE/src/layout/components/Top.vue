<template>
  <header class="top-home">
    <div class="container d-flex justify-content-between align-items-center h-100">
      <!-- Left: Logo -->
      <div class="brand" @click="$router.push('/')">
        <img class="logo" src="../../assets/image/logo.png" alt="DKMN Logo" />
        <!-- <span class="title ms-2">DKMN</span> -->
      </div>

      <!-- Center: Old Nav Links -->
      <nav class="nav-links d-none d-md-flex align-items-center gap-4">
        <a href="#" class="nav-link-custom" @click.prevent="goToSearch">Tìm vé</a>
        <a href="#features" class="nav-link-custom">Tính năng</a>
        <a href="#about" class="nav-link-custom">Giới thiệu</a>
        <a href="#contact" class="nav-link-custom">Liên hệ</a>
      </nav>

      <!-- Right: Actions & Auth -->
      <div class="d-flex align-items-center gap-3">
        <!-- Notification Bell -->
        <div class="dropdown">
           <button class="btn-icon-vexere position-relative" data-bs-toggle="dropdown" aria-expanded="false">
              <i class='bx bxs-bell'></i>
              <span v-if="unreadCount > 0" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">
                {{ unreadCount }}
              </span>
           </button>
           <div class="dropdown-menu dropdown-menu-end shadow border-0 p-0" style="width: 320px; max-height: 450px; overflow-y: auto;">
              <div class="p-3 border-bottom d-flex justify-content-between align-items-center bg-light">
                 <h6 class="m-0 fw-bold text-primary">Thông báo</h6>
                 <small v-if="notifications.length" class="text-muted cursor-pointer" @click="markAllRead">Đã đọc tất cả</small>
              </div>
              <div v-if="notifications.length === 0" class="p-4 text-center text-muted">
                 <i class='bx bx-bell-off fs-1 mb-2'></i>
                 <p class="mb-0 small">Không có thông báo mới.</p>
              </div>
              <ul class="list-group list-group-flush" v-else>
                 <li 
                   v-for="notif in notifications" 
                   :key="notif.id" 
                   class="list-group-item d-flex align-items-start gap-2 p-3 border-bottom action-item"
                   :class="{ 'bg-light': !notif.read }"
                   @click="handleNotificationClick(notif)"
                 >
                    <div class="icon-box bg-blue-light text-primary rounded-circle p-2" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                       <i class='bx border-2' :class="getIcon(notif.type)"></i>
                    </div>
                    <div>
                        <p class="mb-1 fw-semibold small text-dark" style="line-height:1.4;">{{ notif.message }}</p>
                        <small class="text-muted" style="font-size: 0.75rem;">{{ formatTime(notif.createdAt) }}</small>
                    </div>
                 </li>
              </ul>
           </div>
        </div>
        
        <div class="auth-buttons" v-if="!isLoggedIn">
           <router-link to="/client-login" class="btn-auth">Đăng nhập</router-link>
           <router-link to="/client-register" class="btn-auth outline">Đăng ký</router-link>
        </div>

        <div class="user-menu" v-else>
           <div class="dropdown">
            <button class="btn btn-sm text-white dropdown-toggle d-flex align-items-center gap-1" type="button" data-bs-toggle="dropdown">
              <div class="avatar-circle">
                 {{ displayName.charAt(0).toUpperCase() }}
              </div>
              <span class="d-none d-md-block">{{ displayName }}</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-2">
              <li><a class="dropdown-item" href="#" @click.prevent="$router.push('/client-profile')"><i class="bx bx-user me-2"></i>Thông tin cá nhân</a></li>
              <li><a class="dropdown-item" href="#" @click.prevent="$router.push('/client-change-password')"><i class="bx bx-lock-alt me-2"></i>Đổi mật khẩu</a></li>
              <li><a class="dropdown-item" href="#" @click.prevent="$router.push('/client-ve-da-dat')"><i class="bx bx-receipt me-2"></i>Vé đã đặt</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="#" @click="logout"><i class="bx bx-log-out me-2"></i>Đăng xuất</a></li>
            </ul>
           </div>
        </div>
      </div>
    </div>
  </header>
</template>

<script>
import api from '../../services/api'

export default {
  data() {
    return {
      isLoggedIn: false,
      userInfo: {},
      notifications: [],
      unreadCount: 0,
      notifInterval: null,
    }
  },
  computed: {
    displayName() {
      return (
        this.userInfo?.ho_va_ten ||
        this.userInfo?.ho_ten ||
        this.userInfo?.name ||
        this.userInfo?.email ||
        'Người dùng DKMN'
      )
    },
  },
  mounted() {
    this.syncAuthState()

    if (typeof window !== 'undefined') {
      window.addEventListener('storage', this.syncAuthState)
      window.addEventListener('dkmn:auth-changed', this.syncAuthState)
      // Poll notifications every 30s
      this.notifInterval = setInterval(this.fetchNotifications, 30000);
      this.fetchNotifications();
    }
  },
  beforeUnmount() {
    if (typeof window !== 'undefined') {
      window.removeEventListener('storage', this.syncAuthState)
      window.removeEventListener('dkmn:auth-changed', this.syncAuthState)
      if(this.notifInterval) clearInterval(this.notifInterval);
    }
  },
  methods: {
    syncAuthState() {
      if (typeof window === 'undefined') {
        this.userInfo = {}
        this.isLoggedIn = false
        return
      }

      try {
        const token = window.localStorage.getItem('token') || window.localStorage.getItem('key_client')
        const raw = window.localStorage.getItem('userInfo')

        if (token && raw) {
          this.userInfo = JSON.parse(raw)
          this.isLoggedIn = true
          this.fetchNotifications()
        } else {
          this.userInfo = {}
          this.isLoggedIn = false
          this.notifications = []
        }
      } catch (error) {
        this.userInfo = {}
        this.isLoggedIn = false
      }
    },
    logout() {
      if (typeof window !== 'undefined') {
        window.localStorage.removeItem('token')
        window.localStorage.removeItem('key_client')
        window.localStorage.removeItem('userInfo')
      }
      this.userInfo = {}
      this.isLoggedIn = false

      if (typeof window !== 'undefined') {
        window.dispatchEvent(new CustomEvent('dkmn:auth-changed'))
      }

      this.$router.push('/client-login')
    },
    goToSearch() {
      const scrollToSearchSection = () => {
        const searchSection = document.getElementById('search') || document.querySelector('.search-form')
        if (searchSection) {
          searchSection.scrollIntoView({ behavior: 'smooth', block: 'center' })
        }
      }
      
      // Nếu đang ở trang chủ, scroll đến phần đặt vé
      if (this.$route.path === '/' || this.$route.path === '/TrangChu') {
        scrollToSearchSection()
      } else {
        // Nếu không ở trang chủ, chuyển về trang chủ rồi scroll
        this.$router.push('/').then(() => {
          setTimeout(scrollToSearchSection, 500)
        })
      }
    },
    async fetchNotifications() {
        if (!this.isLoggedIn) return;
        try {
            const { data } = await api.get('/dkmn/thong-bao');
            this.notifications = data.data || [];
            this.unreadCount = this.notifications.filter(n => !n.read).length;
        } catch (e) {
            console.error("Fetch notif error", e);
        }
    },
    async markAllRead() {
        try {
            await api.post('/dkmn/thong-bao/mark-read');
            this.notifications.forEach(n => n.read = true);
            this.unreadCount = 0;
        } catch (e) {}
    },
    handleNotificationClick(notif) {
        // Mark as read locally first
        if (!notif.read) {
            notif.read = true;
            this.unreadCount = Math.max(0, this.unreadCount - 1);
            // Optional: call API to mark single read if needed
        }
        // Navigate if needed
    },
    getIcon(type) {
        const icons = {
            'order': 'bx-receipt',
            'system': 'bx-info-circle',
            'promotion': 'bx-gift',
            'cancel': 'bx-x-circle'
        };
        return icons[type] || 'bx-bell';
    },
    formatTime(dateStr) {
        if (!dateStr) return '';
        const date = new Date(dateStr);
        const now = new Date();
        const diff = (now - date) / 1000; // seconds
        if (diff < 60) return 'Vừa xong';
        if (diff < 3600) return `${Math.floor(diff/60)} phút trước`;
        if (diff < 86400) return `${Math.floor(diff/3600)} giờ trước`;
        return date.toLocaleDateString('vi-VN');
    }
  },
}
</script>

<style scoped>

.top-home {
  background-color: #2474E5; /* Vexere Blue */
  height: 70px;
  position: sticky;
  top: 0;
  z-index: 1000;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.logo {
  height: 48px;
  width: auto;
  /* filter: brightness(0) invert(1); Removed to show original logo colors */
}

.nav-link-custom {
  color: #fff;
  text-decoration: none;
  font-weight: 500;
  font-size: 15px;
  padding: 8px 12px;
  border-radius: 4px;
  transition: background 0.2s;
}

.nav-link-custom:hover {
  background: rgba(255,255,255,0.1);
  color: #fff;
}

.btn-icon-vexere {
  background: rgba(255,255,255,0.1);
  border: none;
  width: 36px;
  height: 36px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  cursor: pointer;
  transition: all 0.2s;
}
.btn-icon-vexere:hover, .btn-icon-vexere[aria-expanded="true"] {
    background: rgba(255,255,255,0.2);
}

.action-item {
    cursor: pointer;
    transition: background 0.2s;
}
.action-item:hover {
    background: #f8f9fa;
}
.bg-blue-light { background: #e0f2fe; }
.cursor-pointer { cursor: pointer; }

.btn-auth {
  background: #FFF;
  color: #2474E5;
  padding: 6px 16px;
  border-radius: 4px;
  font-weight: 600;
  text-decoration: none;
  font-size: 14px;
  border: 1px solid #FFF;
  transition: all 0.2s;
}

.btn-auth.outline {
  background: transparent;
  color: #FFF;
  border: 1px solid #FFF;
  margin-left: 8px;
}

.avatar-circle {
  width: 32px;
  height: 32px;
  background: rgba(255,255,255,0.2);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
}

.dropdown-item {
  font-size: 14px;
  padding: 8px 16px;
}

</style>

