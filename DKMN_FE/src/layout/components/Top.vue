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
        <div class="notice-bell dropdown">
          <button class="notice-trigger" type="button" data-bs-toggle="dropdown" @click="fetchNotices">
            <i class="bx bx-bell"></i>
            <span class="notice-badge">{{ unreadCount }}</span>
          </button>
          <div class="dropdown-menu dropdown-menu-end notice-panel">
            <div class="notice-tabs">
              <button
                class="tab-btn"
                :class="{ active: activeNoticeTab === 'notifications' }"
                @click="activeNoticeTab = 'notifications'"
              >
                Thông báo ({{ notices.length }})
              </button>
              <button
                class="tab-btn"
                :class="{ active: activeNoticeTab === 'inbox' }"
                @click="activeNoticeTab = 'inbox'"
              >
                Hộp thư đến ({{ inbox.length }})
              </button>
            </div>

            <div class="notice-body">
              <div
                v-if="activeNoticeTab === 'notifications' && noticesLoading"
                class="notice-empty text-muted"
              >
                Đang tải thông báo...
              </div>
              <div
                v-else-if="activeNoticeTab === 'inbox' && inboxLoading"
                class="notice-empty text-muted"
              >
                Đang tải hộp thư...
              </div>
              <div
                v-else-if="activeNoticeTab === 'notifications' && noticesError"
                class="notice-empty text-danger"
              >
                {{ noticesError }}
              </div>
              <div
                v-else-if="activeNoticeTab === 'inbox' && inboxError"
                class="notice-empty text-danger"
              >
                {{ inboxError }}
              </div>
              <template v-else>
                <div
                  v-if="activeNoticeTab === 'notifications'"
                  class="notice-list"
                role="list"
              >
                <div
                  v-for="item in notices"
                  :key="item.id"
                  class="notice-card"
                  role="listitem"
                  @click="openNotice(item)"
                >
                  <div class="notice-avatar">
                    <span>{{ item.title?.[0] || 'N' }}</span>
                  </div>
                  <div class="notice-content">
                      <div class="notice-title">{{ item.title }}</div>
                      <div class="notice-text">{{ item.message }}</div>
                      <div class="notice-time">{{ formatNoticeTime(item.createdAt) }}</div>
                    </div>
                    <button
                      class="notice-delete btn btn-link p-0"
                      title="Đánh dấu đã đọc"
                      @click.stop="markAsRead(item, 'notifications')"
                    >
                      <i class="bx bx-x"></i>
                    </button>
                  </div>
                  <div v-if="!notices.length" class="notice-empty text-muted">Chưa có thông báo</div>
                </div>

                <div
                  v-else
                  class="notice-list"
                  role="list"
                >
                  <div
                    v-for="item in inbox"
                    :key="item.id"
                    class="notice-card"
                    role="listitem"
                    @click="openNotice(item)"
                  >
                    <div class="notice-avatar inbox-avatar">
                      <i class="bx bx-envelope"></i>
                    </div>
                    <div class="notice-content">
                      <div class="notice-title">
                        {{ item.title }}
                        <span v-if="item.status" class="notice-status" :data-status="item.status">
                          {{ formatInboxStatus(item.status) }}
                        </span>
                      </div>
                      <div class="notice-text">{{ item.message }}</div>
                      <div v-if="item.orderCode" class="notice-meta">Mã đơn: {{ item.orderCode }}</div>
                      <div class="notice-time">{{ formatNoticeTime(item.createdAt) }}</div>
                    </div>
                    <button
                      class="notice-delete btn btn-link p-0"
                      title="Đánh dấu đã đọc"
                      @click.stop="markAsRead(item, 'inbox')"
                    >
                      <i class="bx bx-x"></i>
                    </button>
                  </div>
                  <div v-if="!inbox.length" class="notice-empty text-muted">
                    {{ inboxError || 'Bạn chưa có thư đến.' }}
                  </div>
                </div>
              </template>
            </div>
          </div>
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
              <li><a class="dropdown-item" href="#" @click.prevent="$router.push('/client-change-password')"><i class="bx bx-lock-alt me-2"></i>Đổi mật khẩu</a></li>
              <li><a class="dropdown-item" href="#" @click.prevent="$router.push('/client-ve-da-dat')"><i class="bx bx-receipt me-2"></i>Vé đã đặt</a></li>
           
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="#" @click="logout"><i class="bx bx-log-out me-2"></i>Đăng xuất</a></li>
            </ul>
          </div>
        </div>
      </nav>
    </div>

    <div v-if="activeNotice" class="notice-modal-backdrop" @click="closeNotice">
      <div class="notice-modal" @click.stop>
        <div class="notice-modal__header">
          <div class="badge badge-light text-uppercase text-muted">Thông báo</div>
          <button class="btn btn-link text-muted p-0" @click="closeNotice"><i class="bx bx-x fs-4"></i></button>
        </div>
        <div class="notice-modal__body">
          <div class="notice-modal__icon">
            <i class="bx bx-bell"></i>
          </div>
          <div class="notice-modal__content">
            <h5 class="mb-1">{{ activeNotice.title }}</h5>
            <div class="text-muted small mb-2">{{ formatNoticeTime(activeNotice.createdAt) }}</div>
            <div v-if="activeNoticeDetails.length" class="notice-details mb-2">
              <div class="notice-detail" v-for="item in activeNoticeDetails" :key="item.label">
                <span class="notice-detail-label">{{ item.label }}</span>
                <span class="notice-detail-value">{{ item.value }}</span>
              </div>
            </div>
            <p v-else class="mb-2">{{ activeNotice.message }}</p>
            <div class="d-flex gap-2">
              <button class="btn btn-outline-secondary btn-sm" @click="closeNotice">Đóng</button>
              <button class="btn btn-primary btn-sm" @click="markAsRead(activeNotice, activeNoticeTab)">Đánh dấu đã đọc</button>
            </div>
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
      notices: [],
      noticesLoading: false,
      noticesError: '',
      inbox: [],
      inboxLoading: false,
      inboxError: '',
      inboxFetched: false,
      activeNotice: null,
      activeNoticeTab: 'notifications',
    }
  },
  computed: {
    unreadCount() {
      const noticesUnread = this.notices.filter((n) => !n.read).length || 0
      const inboxUnread = this.inbox.filter((n) => !n.read).length || 0
      return noticesUnread + inboxUnread
    },
    activeNoticeDetails() {
      if (!this.activeNotice?.message) return []
      const msg = this.activeNotice.message
      const details = []

      const code = msg.match(/mã\s+([A-Z0-9-]+)/i)
      if (code?.[1]) {
        details.push({ label: 'Mã vé', value: code[1] })
      }

      const routeMatch = msg.match(/chuyến\s+(.+?)(?:\.|\sGiờ|$)/i)
      if (routeMatch?.[1]) {
        details.push({ label: 'Hành trình', value: routeMatch[1].trim() })
      }

      const depart = msg.match(/Giờ khởi hành:\s*([^\s].*?)(?:\s+Ghế:|$)/i)
      if (depart?.[1]) {
        details.push({ label: 'Giờ khởi hành', value: depart[1].trim() })
      }

      const seat = msg.match(/Ghế:\s*([^\s].*?)(?:\s+Tổng tiền:|$)/i)
      if (seat?.[1]) {
        details.push({ label: 'Ghế', value: seat[1].trim() })
      }

      const total = msg.match(/Tổng tiền:\s*([0-9\.,\s]+[đd]?)/i)
      if (total?.[1]) {
        details.push({ label: 'Tổng tiền', value: total[1].trim() })
      }

      return details
    },
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
    this.fetchNotices()

    if (typeof window !== 'undefined') {
      window.addEventListener('storage', this.syncAuthState)
      window.addEventListener('dkmn:auth-changed', this.syncAuthState)
      window.addEventListener('dkmn:refresh-notices', this.fetchNotices)
    }
  },
  beforeUnmount() {
    if (typeof window !== 'undefined') {
      window.removeEventListener('storage', this.syncAuthState)
      window.removeEventListener('dkmn:auth-changed', this.syncAuthState)
      window.removeEventListener('dkmn:refresh-notices', this.fetchNotices)
    }
  },
  watch: {
    activeNoticeTab(newVal) {
      if (newVal === 'inbox' && !this.isLoggedIn) {
        this.inbox = []
        this.inboxError = 'Vui lòng đăng nhập để xem hộp thư.'
        return
      }
      if (newVal === 'inbox' && !this.inboxFetched && this.isLoggedIn) {
        this.fetchInbox()
      }
    },
  },
  methods: {
    async fetchNotices() {
      this.noticesLoading = true
      this.noticesError = ''
      try {
        let list = []
        if (this.isLoggedIn) {
          const res = await api.get('/dkmn/thong-bao')
          list = res?.data?.data || res?.data || []
        } else {
          const res = await api.get(
            import.meta.env?.VITE_PORTAL_NOTICES_ENDPOINT || '/dkmn/portal/thong-bao'
          )
          list = res?.data?.data || res?.data || []
        }

        this.notices = list
          .map((n, idx) => ({
            id: n.id || n.ma || idx,
            title: n.title || n.subject || n.tieu_de || 'Thông báo',
            message: n.message || n.noi_dung || n.content || '',
            createdAt: n.created_at || n.createdAt || n.ngay_tao || n.datetime || '',
            read: Boolean(n.read || n.da_doc),
          }))
          .filter((n) => n.message)
          .slice(0, 10)
      } catch (error) {
        this.noticesError = 'Không tải được thông báo.'
      } finally {
        this.noticesLoading = false
      }
    },
    async fetchInbox() {
      this.inboxLoading = true
      this.inboxError = ''
      try {
        const res = await api.get('/dkmn/inbox', { params: { limit: 10 } })
        const list = res?.data?.data || res?.data || []
        this.inbox = list
          .map((n, idx) => ({
            id: n.id || idx,
            title: n.title || n.tieu_de || 'Tin nhắn',
            message: n.message || n.noi_dung || n.tra_loi || '',
            createdAt: n.created_at || n.createdAt || n.ngay_tao || n.ngay_cap_nhat || '',
            orderCode: n.orderCode || n.ma_don || n.ma || n.order_code || null,
            status: n.status || n.trang_thai || null,
            read: Boolean(n.read || n.da_doc || ['da_tra_loi', 'dong', true].includes(n.trang_thai)),
          }))
          .filter((n) => n.message)
          .slice(0, 10)
        this.inboxFetched = true
      } catch (error) {
        this.inboxError = 'Không tải được hộp thư đến.'
      } finally {
        this.inboxLoading = false
      }
    },
    async markAsRead(item, source = 'notifications') {
      if (source === 'inbox') {
        const previous = [...this.inbox]
        this.inbox = this.inbox.filter((n) => n.id !== item.id)
        if (this.activeNotice?.id === item.id) {
          this.activeNotice = null
        }
        try {
          await api.post('/dkmn/inbox/mark-read', { ids: [item.id] })
        } catch (error) {
          console.warn('Cannot mark inbox as read', error)
          this.inbox = previous
          if (!this.activeNotice && previous.find((n) => n.id === item.id)) {
            this.activeNotice = item
          }
        }
        return
      }

      const previous = [...this.notices]
      this.notices = this.notices.filter((n) => n.id !== item.id)
      if (this.activeNotice?.id === item.id) {
        this.activeNotice = null
      }

      try {
        await api.post('/dkmn/thong-bao/mark-read', { ids: [item.id] })
      } catch (error) {
        console.warn('Cannot mark notification as read', error)
        this.notices = previous
        if (!this.activeNotice && previous.find((n) => n.id === item.id)) {
          this.activeNotice = item
        }
      }
    },
    openNotice(notice) {
      this.activeNotice = notice
    },
    closeNotice() {
      this.activeNotice = null
    },
    formatNoticeTime(ts) {
      if (!ts) return ''
      try {
        const d = new Date(ts)
        if (Number.isNaN(d.getTime())) return ts
        return d.toLocaleString('vi-VN')
      } catch (e) {
        return ts
      }
    },
    syncAuthState() {
      if (typeof window === 'undefined') {
        this.userInfo = {}
        this.isLoggedIn = false
        return
      }

      const previous = this.isLoggedIn

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

        if (previous !== this.isLoggedIn) {
        this.notices = []
        this.inbox = []
        this.inboxFetched = false
        this.fetchNotices()
        if (this.isLoggedIn && this.activeNoticeTab === 'inbox') {
          this.fetchInbox()
        }
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
      this.notices = []
      this.inbox = []
      this.inboxFetched = false
      this.activeNotice = null

      if (typeof window !== 'undefined') {
        window.dispatchEvent(new CustomEvent('dkmn:auth-changed'))
      }

      this.$router.push('/client-login')
    },
    formatInboxStatus(status) {
      const map = {
        moi: 'Mới',
        dang_xu_ly: 'Đang xử lý',
        da_tra_loi: 'Đã trả lời',
        dong: 'Đóng',
      }
      return map[status] || status
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

.notice-bell {
  position: relative;
}
.notice-trigger {
  position: relative;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 42px;
  height: 42px;
  border-radius: 50%;
  border: 1px solid rgba(255,255,255,0.35);
  background: rgba(255,255,255,0.12);
  color: #fff;
  transition: all 0.2s ease;
}
.notice-trigger:hover {
  border-color: #fff;
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
.notice-badge {
  position: absolute;
  top: -6px;
  right: -6px;
  min-width: 18px;
  height: 18px;
  border-radius: 999px;
  background: #e63946;
  color: #fff;
  font-size: 11px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0 4px;
  border: 2px solid #764ba2;
  box-shadow: 0 2px 6px rgba(0,0,0,0.2);
}
.notice-panel {
  width: 320px;
  padding: 0;
  border: 1px solid #e5e7eb;
  box-shadow: 0 12px 30px rgba(17, 24, 39, 0.22);
  border-radius: 14px;
  overflow: hidden;
}
.notice-tabs {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  background: #f3f4f6;
}
.tab-btn {
  border: none;
  background: transparent;
  padding: 10px;
  font-weight: 600;
  color: #6b7280;
  transition: all 0.2s ease;
}
.tab-btn.active {
  background: #fff;
  color: #111827;
  box-shadow: inset 0 -3px #1f85ff;
}
.notice-body {
  max-height: 360px;
  overflow-y: auto;
  padding: 10px;
  background: #fff;
}
.notice-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
}
.notice-card {
  position: relative;
  display: grid;
  grid-template-columns: auto 1fr;
  align-items: start;
  gap: 10px 6px;
  padding: 12px 14px;
  padding-right: 38px;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.2s ease;
}
.notice-card:hover {
  border-color: #1f85ff;
  box-shadow: 0 6px 16px rgba(31, 133, 255, 0.12);
}
.notice-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: linear-gradient(135deg, #1f85ff, #764ba2);
  color: #fff;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  text-transform: uppercase;
}
.inbox-avatar {
  font-size: 1.1rem;
}
.notice-content {
  min-width: 0;
}
.notice-title {
  font-weight: 700;
  margin-bottom: 2px;
  color: #111827;
}
.notice-status {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 2px 8px;
  margin-left: 6px;
  border-radius: 10px;
  font-size: 11px;
  font-weight: 600;
  text-transform: capitalize;
  background: #eef2ff;
  color: #4338ca;
}
.notice-status[data-status='dang_xu_ly'] {
  background: #fff7ed;
  color: #c2410c;
}
.notice-status[data-status='da_tra_loi'],
.notice-status[data-status='dong'] {
  background: #ecfdf3;
  color: #15803d;
}
.notice-meta {
  color: #6b7280;
  font-size: 0.85rem;
  margin-top: 2px;
}
.notice-text {
  color: #4b5563;
  font-size: 0.9rem;
  line-height: 1.4;
}
.notice-time {
  color: #9ca3af;
  font-size: 0.82rem;
  margin-top: 4px;
}
.notice-delete {
  align-self: flex-start;
  color: #9ca3af;
  font-size: 1.1rem;
  position: absolute;
  top: 8px;
  right: 8px;
  margin: 0;
}
.notice-delete:hover {
  color: #ef4444;
}
.notice-empty {
  padding: 16px 12px;
  text-align: center;
}
.notice-modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.4);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 16px;
  z-index: 2000;
  backdrop-filter: blur(2px);
}
.notice-modal {
  background: linear-gradient(135deg, #f8fbff, #fff);
  border-radius: 16px;
  padding: 22px 22px 18px;
  width: min(520px, 92vw);
  box-shadow: 0 24px 60px rgba(0,0,0,0.15);
  border: 1px solid #e5e7eb;
}
.notice-modal__header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
}
.notice-modal__header .badge {
  background: #f3f4f6;
  color: #6b7280;
  font-weight: 700;
  letter-spacing: 0.3px;
}
.notice-modal__body {
  display: grid;
  grid-template-columns: auto 1fr;
  gap: 12px;
}
.notice-modal__icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  background: linear-gradient(135deg, #1f85ff, #764ba2);
  color: #fff;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 22px;
  box-shadow: 0 8px 18px rgba(55, 112, 255, 0.25);
}
.notice-modal__content h5 {
  font-weight: 700;
  margin-bottom: 4px;
}
.notice-details {
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.notice-detail {
  display: flex;
  gap: 6px;
  flex-wrap: wrap;
}
.notice-detail-label {
  font-weight: 600;
  color: #4b5563;
}
.notice-detail-label::after {
  content: ':';
  margin-left: 3px;
}
.notice-detail-value {
  color: #111827;
}
.notice-modal .text-muted.small {
  letter-spacing: 0.2px;
}
.notice-modal .btn {
  min-width: 110px;
  border-radius: 24px;
}
.notice-modal .btn-primary {
  background: linear-gradient(135deg, #1f85ff, #2563eb);
  border: none;
  box-shadow: 0 6px 18px rgba(31, 133, 255, 0.25);
}
.notice-modal .btn-outline-secondary {
  color: #4b5563;
  border-color: #d1d5db;
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
