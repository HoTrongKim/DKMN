/<template>

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
            <router-link to="/Client-Login" class="btn btn-outline-light btn-sm me-2">
              <i class="bx bx-log-in me-1"></i>
              Đăng nhập
            </router-link>
            <router-link to="/Client-Register" class="btn btn-primary btn-sm">
              <i class="bx bx-user-plus me-1"></i>
              Đăng ký
            </router-link>
          </div>
          
          <!-- User Menu (when logged in) -->
          
          <div class="user-menu" v-else>
            <div class="dropdown">
              <button class="btn btn-outline-light btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="bx bx-user me-1"></i>
                {{ userInfo.name || userInfo.email }}
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
      userInfo: {}
    }
  },
  mounted() {
    // GIẢ LẬP tài khoản mặc định
    this.userInfo = {
      name: "Người dùng DKMN",
      email: "demo@dkmn.test"
    }
    this.isLoggedIn = true
  },
  methods: {
    logout() {
      this.isLoggedIn = false
      this.userInfo = {}
    }
  }
}
</script>

<style>

</style>