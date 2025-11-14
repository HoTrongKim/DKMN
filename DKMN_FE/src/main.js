// Bootstrap (từ npm) — chỉ cần 2 dòng này
import 'bootstrap/dist/css/bootstrap.min.css'
import 'bootstrap'

import { createApp } from 'vue'
import App from './App.vue'
import router from './router'

// Layouts
import Default from './layout/wrapper/index_admin.vue'
import Client from './layout/wrapper/index_client.vue'

// (TÙY CHỌN) CSS của bạn — CHỈ import những file tự viết/tuỳ biến,
// KHÔNG import lại bootstrap.min.css bản copy local để tránh trùng
import './assets/css/icons.css'
import './assets/css/app.css'

// ⚠️ BỎ DÒNG NÀY để tránh nạp JS Bootstrap lần 2 (đã có 'bootstrap' ở trên)
// import './assets/js/bootstrap.bundle.min.js'

const app = createApp(App)
app.use(router)
app.component('default-layout', Default)
app.component('client-layout', Client)
// Ensure admin routes (meta.layout === 'admin') resolve to admin shell
app.component('admin-layout', Default)
app.mount('#app')
