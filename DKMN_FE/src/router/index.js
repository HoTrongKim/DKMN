// import { createRouter, createWebHistory } from "vue-router"; // cài vue-router: npm install vue-router@next --save
// const AdminWrapper  = () => import("../layout/wrapper/index_admin.vue");
// const MenuAdmin     = () => import("../layout/components/MenuAdmin.vue");
// const QuanLiTrangCHu = () => import('../components/Admin/QuanLiTrangChu/AdminHome.vue');
// const QuanLiChuyenDi = () => import('../components/Admin/QuanLiChuyenDi/QuanLiChuyenDi.vue');
// const QuanLiDonHang = () => import('../components/Admin/QuanLiDonHang/QuanLiDonHang.vue');
// const QuanLiNguoiDung = () => import('../components/Admin/QuanLiNguoiDung/QuaLiNguoiDung.vue');
// const QuanLiThanhToan = () => import('../components/Admin/QuanLiThanhToan/QuanLiThanhToan.vue');


// const routes = [
// 	// {
// 	// 	path: '/',
// 	// 	component: () => import('../components/Client/TrangChu/TrangChu.vue'),
// 	// 	meta: { layout: 'client' }
// 	// },
// 	{ path: '/view/admin', component: () => import('../layout/components/MenuAdmin.vue') },

// 	// --- TUYẾN XEM GIAO DIỆN ADMIN (TRUY CẬP TRỰC TIẾP) ---
//     { 
//         path: '/view/chuyendi', 
//         name: 'ViewTrips', 
//         component: QuanLiChuyenDi, 
//         meta: { layout: 'admin' } 
//     },
// 	{ path: '', component: AdminHome }, 
//     { 
//         path: '/view/donhang', 
//         name: 'ViewOrders', 
//         component: QuanLiDonHang, 
//         meta: { layout: 'admin' } 
//     },
//     { 
//         path: '/view/nguoidung', 
//         name: 'ViewUsers', 
//         component: QuanLiNguoiDung, 
//         meta: { layout: 'admin' } 
//     },
//     { 
//         path: '/view/thanhtoan', 
//         name: 'ViewPayments', 
//         component: QuanLiThanhToan, 
//         meta: { layout: 'admin' } 
//     },
	
    
//     // --- 404 NOT FOUND ---
//     // { 
//     //     path: '/:catchAll(.*)', 
//     //     name: 'NotFound', 
//     //     component: NotFound 
//     // }
// ]

// const router = createRouter({
// 	history: createWebHistory(),
// 	routes: routes
// })

import { createRouter, createWebHistory } from "vue-router";

// Layout admin (có topbar + sidebar + <router-view/>)
const AdminWrapper = () => import("../layout/wrapper/index_admin.vue");

// Trang chủ admin (ở giữa)
const AdminHome = () =>
  import("../components/Admin/QuanLiTrangChu/AdminHome.vue");

// Các trang quản trị
const QuanLiChuyenDi = () =>
  import("../components/Admin/QuanLiChuyenDi/QuanLiChuyenDi.vue");
const QuanLiDonHang = () =>
  import("../components/Admin/QuanLiDonHang/QuanLiDonHang.vue");
const QuanLiNguoiDung = () =>
  import("../components/Admin/QuanLiNguoiDung/QuanLiNguoiDung.vue");
const QuanLiThanhToan = () =>
  import("../components/Admin/QuanLiThanhToan/QuanLiThanhToan.vue");
const QuanLiDanhGia = () =>
  import("../components/Admin/QuanLiDanhGia/QuanLiDanhGia.vue");
const BaoCaoThongKe = () =>
  import("../components/Admin/QuanLiThongKe/BaoCaoThongKe.vue");

// ---------------- ROUTER ---------------- //
const routes = [
  {
    path: "/view",
    component: AdminWrapper, // Layout chứa sidebar + topbar + <router-view/>
    redirect: "/view/home", // luôn tự chuyển đến trang chủ admin khi vào /view
    children: [
      {
        path: "home",
        name: "AdminHome",
        component: AdminHome,
      },
      {
        path: "chuyendi",
        name: "QuanLiChuyenDi",
        component: QuanLiChuyenDi,
      },
      {
        path: "donhang",
        name: "QuanLiDonHang",
        component: QuanLiDonHang,
      },
      {
        path: "nguoidung",
        name: "QuanLiNguoiDung",
        component: QuanLiNguoiDung,
      },
      {
        path: "thanhtoan",
        name: "QuanLiThanhToan",
        component: QuanLiThanhToan,
      },
      {
        path: "danhgia",
        name: "QuanLiDanhGia",
        component: QuanLiDanhGia,
      },
      {
        path: "baocao",
        name: "BaoCaoThongKe",
        component: BaoCaoThongKe,
      },
    ],
  },

  // Điều hướng gốc "/" về "/view/home"
  { path: "/", redirect: "/view/home" },

  // Tránh lỗi route không tồn tại
  {
    path: "/:pathMatch(.*)*",
    redirect: "/view/home",
  },
];

// ---------------- ROUTER INSTANCE ---------------- //
const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
});

export default router;
