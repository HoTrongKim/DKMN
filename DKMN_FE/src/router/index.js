import { createRouter, createWebHistory } from "vue-router"; // cài vue-router: npm install vue-router@next --save

const ADMIN_EMAIL = 'admin@dkmn.com';

const routes = [
	{
		path: '/',
		component: () => import('../components/Client/TrangChu/TrangChu.vue'),
		meta: { layout: 'client' }
	},
	{
		path: '/TrangChu',
		component: () => import('../components/Client/TrangChu/TrangChu.vue'),
		meta: { layout: 'client' }
	},
	{
		path: '/client-login',
		component: () => import('../components/Client/Login/index.vue'),
		meta: { layout: 'client' }
	},
	{
		path: '/client-register',
		component: () => import('../components/Client/Register/index.vue'),
		meta: { layout: 'client' }
	},
  {
    path: '/client-profile',
    component: () => import('../components/Client/Profile/Profile.vue'),
    meta: { layout: 'client' }
  },
	{
		path: '/client-menu',
		component: () => import('../layout/components/MenuClient.vue'),
		meta: { layout: 'client' }
	},
  {
    path: '/client-thanh-toan',
    component: () => import('../components/Client/ThanhToan/ThanhToan.vue'),
    meta: { layout: 'client' }
  },
  {
    path: '/client-ve-da-dat',
    component: () => import('../components/Client/VeDaDat/VeDaDat.vue'),
    meta: { layout: 'client' }
  },
  {
    path: '/client-danh-gia',
    component: () => import('../components/Client/DanhGia/DanhGia.vue'),
    meta: { layout: 'client' }
  },
  ///CLientCLient
  {
    path: "/view/home",
    component: () =>
      import("../components/Admin/QuanLiTrangChu/AdminHome.vue"),
    meta: { layout: "admin", requiresAdmin: true },
  },
  {
    path: "/admin",
    component: () =>
      import("../components/Admin/QuanLiTrangChu/AdminHome.vue"),
    meta: { layout: "admin", requiresAdmin: true },
  },
  {
    path: "/view/chuyendi",
    component: () =>
      import("../components/Admin/QuanLiChuyenDi/QuanLiChuyenDi.vue"),
    meta: { layout: "admin", requiresAdmin: true },
  },
  {
    path: "/view/donhang",
    component: () =>
      import("../components/Admin/QuanLiDonHang/QuanLiDonHang.vue"),
    meta: { layout: "admin", requiresAdmin: true },
  },
  {
    path: "/view/nguoidung",
    component: () =>
      import("../components/Admin/QuanLiNguoiDung/QuanLiNguoiDung.vue"),
    meta: { layout: "admin", requiresAdmin: true },
  },
  {
    path: "/view/thanhtoan",
    component: () =>
      import("../components/Admin/QuanLiThanhToan/QuanLiThanhToan.vue"),
    meta: { layout: "admin", requiresAdmin: true },
  },
  {
    path: "/view/danhgia",
    component: () =>
      import("../components/Admin/QuanLiDanhGia/QuanLiDanhGia.vue"),
    meta: { layout: "admin", requiresAdmin: true },
  },
  {
    path: "/view/baocao",
    component: () =>
      import("../components/Admin/QuanLiThongKe/BaoCaoThongKe.vue"),
    meta: { layout: "admin", requiresAdmin: true },
  },


]

const router = createRouter({
	history: createWebHistory(),
	routes: routes
})

router.beforeEach((to, from, next) => {
  const requiresAdmin = to.meta?.requiresAdmin;
  const requiresAuth = to.meta?.requiresAuth || requiresAdmin;

  if (!requiresAuth) {
    return next();
  }

  if (typeof window === 'undefined') {
    return next();
  }

  const token = localStorage.getItem('token');
  const userInfoRaw = localStorage.getItem('userInfo');

  if (!token || !userInfoRaw) {
    return next({
      path: '/client-login',
      query: { redirect: to.fullPath },
    });
  }

  let role = '';
  let email = '';
  try {
    const parsed = JSON.parse(userInfoRaw);
    role = (parsed.vai_tro || parsed.role || '').toLowerCase();
    email = (parsed.email || '').toLowerCase();
  } catch (error) {
    role = '';
    email = '';
  }

  const isAdmin = role === 'quan_tri' || role === 'admin' || email === ADMIN_EMAIL;

  if (requiresAdmin && !isAdmin) {
    return next('/');
  }

  return next();
});

export default router
