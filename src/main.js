import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import Default from './layout/wrapper/index_admin.vue'
import Client from './layout/wrapper/index_client.vue'

// Global styles for Client UI
import './assets/css/bootstrap.min.css'
import './assets/css/icons.css'
import './assets/css/app.css'

// Vendor JS (Bootstrap)
import './assets/js/bootstrap.bundle.min.js'

const app = createApp(App)

app.use(router)
app.component("default-layout", Default);
app.component("client-layout", Client);
// app.component("Admin-layout", Default);
app.mount("#app")