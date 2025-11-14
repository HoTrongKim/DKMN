<template>
  <header class="top-home">
    <div class="top-home__container">
      <div class="brand" @click="$router.push('/')">
        <img class="logo" src="../../assets/image/logo.png" alt="DKMN Logo" />
        <span class="title">DKMN</span>
      </div>
      <nav class="nav">
        <div class="nav-links">
          <a href="#search" class="nav-link">Tìm vé</a>
          <a href="#features" class="nav-link">Tính năng</a>
          <a href="#about" class="nav-link">Giới thiệu</a>
          <a href="#contact" class="nav-link">Liên hệ</a>
        </div>
        
        <!-- Auth Buttons -->
        <div class="auth-buttons" v-if="!isLoggedIn">
          <router-link to="/client-login" class="btn btn-outline-light btn-sm me-2">
            <i class="bx bx-log-in me-1"></i>
            Đăng nhập
          </router-link>
          <router-link to="/client-register" class="btn btn-primary btn-sm">
            <i class="bx bx-user-plus me-1"></i>
            Đăng ký
          </router-link>
        </div>
        
        <!-- User Menu (when logged in) -->
        
        <div class="user-menu" v-else>
          <div class="dropdown">
            <button class="btn btn-outline-light btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
              <i class="bx bx-user me-1"></i>
              {{ displayName }}
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="#" @click.prevent="$router.push('/client-profile')"><i class="bx bx-user me-2"></i>Thông tin cá nhân</a></li>
              <li><a class="dropdown-item" href="#" @click.prevent="$router.push('/client-ve-da-dat')"><i class="bx bx-receipt me-2"></i>Vé đã đặt</a></li>
           
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="#" @click="logout"><i class="bx bx-log-out me-2"></i>Đăng xuất</a></li>
            </ul>
          </div>
        </div>
      </nav>
    </div>
  </header>
</template>

<script>
export default {
  data() {
    return {
      isLoggedIn: false,
      userInfo: {},
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
    }
  },
  beforeUnmount() {
    if (typeof window !== 'undefined') {
      window.removeEventListener('storage', this.syncAuthState)
      window.removeEventListener('dkmn:auth-changed', this.syncAuthState)
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
        } else {
          this.userInfo = {}
          this.isLoggedIn = false
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
  },
}
</script>

<style scoped>
.top-home {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 1rem 0;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.top-home__container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.brand {
  display: flex;
  align-items: center;
  cursor: pointer;
  transition: transform 0.3s ease;
}

.brand:hover {
  transform: scale(1.05);
}

.logo {
  height: 40px;
  width: auto;
  margin-right: 0.5rem;
}

.title {
  font-size: 1.5rem;
  font-weight: bold;
  color: white;
  text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
}

.nav {
  display: flex;
  align-items: center;
  gap: 2rem;
}

.nav-links {
  display: flex;
  gap: 1.5rem;
}

.nav-link {
  color: white;
  text-decoration: none;
  font-weight: 500;
  transition: color 0.3s ease;
  padding: 0.5rem 1rem;
  border-radius: 5px;
}

.nav-link:hover {
  color: #ffd700;
  background-color: rgba(255,255,255,0.1);
}

.auth-buttons {
  display: flex;
  gap: 0.5rem;
}

.user-menu {
  display: flex;
  align-items: center;
}

.btn {
  border-radius: 25px;
  padding: 0.5rem 1rem;
  font-weight: 500;
  transition: all 0.3s ease;
}

.btn-outline-light {
  border-color: white;
  color: white;
}

.btn-outline-light:hover {
  background-color: white;
  color: #667eea;
}

.btn-primary {
  border-color: white;
  color: white;
}

.btn-primary:hover {
  background-color: white;
  color: #667eea;
}

.dropdown-menu {
  border-radius: 10px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.15);
  border: none;
}

.dropdown-item {
  padding: 0.75rem 1rem;
  transition: background-color 0.3s ease;
}

.dropdown-item:hover {
  background-color: #f8f9fa;
}

.dropdown-item i {
  color: #667eea;
}

/* Responsive */
@media (max-width: 768px) {
  .top-home__container {
    padding: 0 1rem;
    flex-direction: column;
    gap: 1rem;
  }
  
  .nav {
    flex-direction: column;
    gap: 1rem;
  }
  
  .nav-links {
    flex-wrap: wrap;
    justify-content: center;
  }
  
  .auth-buttons {
    justify-content: center;
  }
}
</style>
