import { createRouter, createWebHistory } from "vue-router"; // cài vue-router: npm install vue-router@next --save

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
		path: '/client-menu',
		component: () => import('../layout/components/MenuClient.vue'),
		meta: { layout: 'client' }
	},
  ///CLientCLient
  {
    path: "/view/home",
    component: () =>
      import("../components/Admin/QuanLiTrangChu/AdminHome.vue"),
    meta: { layout: "admin" },
  },
  {
    path: "/view/chuyendi",
    component: () =>
      import("../components/Admin/QuanLiChuyenDi/QuanLiChuyenDi.vue"),
    meta: { layout: "admin" },
  },
  {
    path: "/view/donhang",
    component: () =>
      import("../components/Admin/QuanLiDonHang/QuanLiDonHang.vue"),
    meta: { layout: "admin" },
  },
  {
    path: "/view/nguoidung",
    component: () =>
      import("../components/Admin/QuanLiNguoiDung/QuanLiNguoiDung.vue"),
    meta: { layout: "admin" },
  },
  {
    path: "/view/thanhtoan",
    component: () =>
      import("../components/Admin/QuanLiThanhToan/QuanLiThanhToan.vue"),
    meta: { layout: "admin" },
  },
  {
    path: "/view/danhgia",
    component: () =>
      import("../components/Admin/QuanLiDanhGia/QuanLiDanhGia.vue"),
    meta: { layout: "admin" },
  },
  {
    path: "/view/baocao",
    component: () =>
      import("../components/Admin/QuanLiThongKe/BaoCaoThongKe.vue"),
    meta: { layout: "admin" },
  },


]

const router = createRouter({
	history: createWebHistory(),
	routes: routes
})

export default router