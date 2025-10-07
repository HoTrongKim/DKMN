import { createRouter, createWebHistory } from "vue-router"; // cài vue-router: npm install vue-router@next --save

const routes = [

  // {
  //   path: '/admin/login',
  //   component: () => import('../components/Admin/Login/index.vue'),
  //   meta: { layout: "blank" },
  // }, 
 
]

const router = createRouter({
  history: createWebHistory(),
  routes: routes
})

export default router