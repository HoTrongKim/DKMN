<?php

use App\Http\Controllers\Admin\DashboardAdminController;
use App\Http\Controllers\Admin\OrderAdminController;
use App\Http\Controllers\Admin\PaymentAdminController;
use App\Http\Controllers\Admin\TripAdminController;
use App\Http\Controllers\Admin\UserAdminController;
use App\Http\Controllers\Admin\RatingAdminController;
use App\Http\Controllers\Admin\NotificationAdminController;
use App\Http\Controllers\Client\OrderClientController;
use App\Http\Controllers\Client\RatingClientController;
use App\Http\Controllers\CauHinhHeThongController;
use App\Http\Controllers\ChiTietDonHangController as DkmnChiTietDonHangController;
use App\Http\Controllers\ChiTietPhiDonHangController;
use App\Http\Controllers\ChuyenDiController;
use App\Http\Controllers\DanhGiaController;
use App\Http\Controllers\DonHangController;
use App\Http\Controllers\GheController;
use App\Http\Controllers\NguoiDungController;
use App\Http\Controllers\NguoiDungQuyenHanController;
use App\Http\Controllers\NhaVanHanhController;
use App\Http\Controllers\NhatKyHoatDongController;
use App\Http\Controllers\PhanHoiController;
use App\Http\Controllers\PhiDichVuController;
use App\Http\Controllers\QuyenHanController as DkmnQuyenHanController;
use App\Http\Controllers\ThanhToanController as DkmnThanhToanController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\ThongBaoController as DkmnThongBaoController;
use App\Http\Controllers\ThongKeDoanhThuController;
use App\Http\Controllers\TinhThanhController;
use App\Http\Controllers\TramController;
use App\Http\Controllers\HuyVeController;
use App\Http\Controllers\LienHeController;
use Illuminate\Support\Facades\Route;

// =========================================================
// ======================= DKMN API ========================
// =========================================================

/*
|--------------------------------------------------------------------------
| Public Authentication Routes
|--------------------------------------------------------------------------
| Các route xác thực không yêu cầu token: Đăng nhập, Đăng ký, Quên mật khẩu
*/
Route::post('/nguoi-dung/dang-nhap', [NguoiDungController::class, 'dangNhap']);
Route::post('/nguoi-dung/dang-ky', [NguoiDungController::class, 'dangKy']);
Route::post('/dkmn/password/forgot', [NguoiDungController::class, 'quenMatKhau']);
Route::post('/dkmn/password/verify-otp', [NguoiDungController::class, 'xacThucOtp']);
Route::post('/dkmn/password/reset', [NguoiDungController::class, 'datLaiMatKhau']);

/*
|--------------------------------------------------------------------------
| Public Data Routes (Prefix: dkmn)
|--------------------------------------------------------------------------
| Các route lấy dữ liệu danh mục dùng chung (tỉnh thành, trạm, nhà vận hành...)
| Không yêu cầu đăng nhập để khách vãng lai có thể tìm kiếm chuyến đi
*/
Route::prefix('dkmn')->group(function () {
    // Danh mục địa chính & nhà vận hành
    Route::get('/tinh-thanh/get-data', [TinhThanhController::class, 'getData']);
    Route::get('/tram/get-data', [TramController::class, 'getData']);
    Route::get('/nha-van-hanh/get-data', [NhaVanHanhController::class, 'getData']);
    
    // Tìm kiếm chuyến đi & lấy sơ đồ ghế
    Route::match(['get', 'post'], '/chuyen-di/search', [ChuyenDiController::class, 'search']);
    Route::get('/chuyen-di/{chuyenDi}/ghe', [GheController::class, 'getByChuyenDi']);
    
    // Cấu hình hệ thống (banner, contact info...)
    Route::get('/cau-hinh/get-data', [CauHinhHeThongController::class, 'getData']);

    // Gửi liên hệ/phản hồi (Contact form)
    Route::post('/lien-he', [LienHeController::class, 'store']);

    // Webhook callbacks (Payment Gateways)
    // Xử lý callback từ ngân hàng/ví điện tử (không auth, verify bằng signature)
    Route::post('/payments/qr/webhook', [PaymentController::class, 'handleQrWebhook']);
    Route::match(['get', 'post'], '/payments/sepay/webhook', [\App\Http\Controllers\PaymentWebhookController::class, 'handleSepay']);
});

/*
|--------------------------------------------------------------------------
| Authenticated User Routes (Prefix: dkmn)
|--------------------------------------------------------------------------
| Các route yêu cầu đăng nhập (auth:sanctum)
| Bao gồm: thông tin cá nhân, đặt vé, thanh toán, thông báo
*/
Route::prefix('dkmn')->middleware('auth:sanctum')->group(function () {
    // Thông tin tài khoản
    Route::get('/me', [NguoiDungController::class, 'thongTin']);
    Route::put('/me', [NguoiDungController::class, 'capNhatThongTin']);
    Route::post('/me/change-password', [NguoiDungController::class, 'doiMatKhau']);

    // Đặt vé & Thanh toán
    Route::post('/don-hang', [DonHangController::class, 'store']); // Tạo đơn hàng mới
    Route::post('/thanh-toan', [DkmnThanhToanController::class, 'store']); // Xác nhận thanh toán thủ công
    
    // Thanh toán Online (QR/Banking)
    Route::post('/payments/qr/init', [PaymentController::class, 'initQr']);
    Route::get('/payments/{payment}/status', [PaymentController::class, 'status']);
    Route::post('/payments/onboard/confirm', [PaymentController::class, 'confirmOnboard']); // Xác nhận thanh toán tại quầy/lên xe

    // Thông báo & Hộp thư
    Route::get('/thong-bao', [DkmnThongBaoController::class, 'me']);
    Route::post('/thong-bao/mark-read', [DkmnThongBaoController::class, 'markAsRead']);
    Route::get('/inbox', [DkmnThongBaoController::class, 'inbox']);
    Route::post('/inbox/mark-read', [DkmnThongBaoController::class, 'markInboxAsRead']);
});

/*
|--------------------------------------------------------------------------
| Legacy Admin Routes (Prefix: dkmn, Role: quan_tri)
|--------------------------------------------------------------------------
| Các route quản trị cũ, trả về dữ liệu dạng danh sách (getData)
| Đang dần chuyển sang nhóm route /admin chuẩn RESTful hơn
*/
Route::prefix('dkmn')->middleware(['auth:sanctum', 'role:quan_tri'])->group(function () {
    Route::get('/chuyen-di/get-data', [ChuyenDiController::class, 'getData']);
    Route::get('/nguoi-dung/get-data', [NguoiDungController::class, 'getData']);
    Route::get('/quyen-han/get-data', [DkmnQuyenHanController::class, 'getData']);
    Route::get('/nguoi-dung-quyen-han/get-data', [NguoiDungQuyenHanController::class, 'getData']);

    Route::get('/don-hang/get-data', [DonHangController::class, 'getData']);
    Route::get('/chi-tiet-don-hang/get-data', [DkmnChiTietDonHangController::class, 'getData']);

    Route::get('/phi-dich-vu/get-data', [PhiDichVuController::class, 'getData']);
    Route::get('/chi-tiet-phi-don-hang/get-data', [ChiTietPhiDonHangController::class, 'getData']);

    Route::get('/thanh-toan/get-data', [DkmnThanhToanController::class, 'getData']);

    Route::get('/huy-ve/get-data', [HuyVeController::class, 'getData']);
    Route::get('/danh-gia/get-data', [DanhGiaController::class, 'getData']);
    Route::get('/phan-hoi/get-data', [PhanHoiController::class, 'getData']);
    Route::get('/thong-bao/get-data', [DkmnThongBaoController::class, 'getData']);
    Route::get('/thong-ke-doanh-thu/get-data', [ThongKeDoanhThuController::class, 'getData']);
    Route::get('/nhat-ky-hoat-dong/get-data', [NhatKyHoatDongController::class, 'getData']);
    
    Route::get('/lien-he/get-data', [LienHeController::class, 'getData']);
    Route::put('/lien-he/{lienHe}', [LienHeController::class, 'update']);
    Route::delete('/lien-he/{lienHe}', [LienHeController::class, 'destroy']);
});

/*
|--------------------------------------------------------------------------
| New Admin Routes (Prefix: admin, Role: quan_tri)
|--------------------------------------------------------------------------
| Các route quản trị mới, thiết kế chuẩn RESTful
| Dùng cho trang Admin Dashboard mới
*/
Route::prefix('admin')->middleware(['auth:sanctum', 'role:quan_tri'])->group(function () {
    // Quản lý chuyến đi (Trips)
    Route::get('/trips', [TripAdminController::class, 'index']);
    Route::get('/trips/{chuyenDi}', [TripAdminController::class, 'show']);
    Route::post('/trips', [TripAdminController::class, 'store']);
    Route::put('/trips/{chuyenDi}', [TripAdminController::class, 'update']);
    Route::post('/trips/{chuyenDi}/notify', [TripAdminController::class, 'notify']); // Gửi thông báo delay/hủy chuyến
    Route::delete('/trips/{chuyenDi}', [TripAdminController::class, 'destroy']);

    // Quản lý đơn hàng (Orders)
    Route::get('/orders', [OrderAdminController::class, 'index']);
    Route::get('/orders/{donHang}', [OrderAdminController::class, 'show']);
    Route::patch('/orders/{donHang}', [OrderAdminController::class, 'update']);
    Route::delete('/orders/{donHang}', [OrderAdminController::class, 'destroy']);

    // Quản lý người dùng (Users)
    Route::get('/users', [UserAdminController::class, 'index']);
    Route::post('/users', [UserAdminController::class, 'store']);
    Route::patch('/users/{nguoiDung}', [UserAdminController::class, 'update']);
    Route::patch('/users/{nguoiDung}/status', [UserAdminController::class, 'updateStatus']); // Khóa/Mở khóa tài khoản
    Route::delete('/users/{nguoiDung}', [UserAdminController::class, 'destroy']);

    // Quản lý thanh toán & Báo cáo (Payments & Stats)
    Route::get('/payments', [PaymentAdminController::class, 'index']);
    Route::get('/payments/export', [PaymentAdminController::class, 'export']); // Xuất Excel
    Route::get('/statistics/overview', [DashboardAdminController::class, 'overview']); // Dashboard stats
    Route::get('/statistics/report', [DashboardAdminController::class, 'report']); // Báo cáo định kỳ

    // Quản lý đánh giá (Ratings)
    Route::get('/ratings', [RatingAdminController::class, 'index']);
    Route::patch('/ratings/{danhGia}', [RatingAdminController::class, 'update']); // Duyệt/Từ chối đánh giá
    Route::delete('/ratings/{danhGia}', [RatingAdminController::class, 'destroy']);

    // Quản lý thông báo hệ thống (Notifications)
    Route::get('/notifications', [NotificationAdminController::class, 'index']);
    Route::post('/notifications', [NotificationAdminController::class, 'store']); // Gửi thông báo hàng loạt
    Route::delete('/notifications/{thongBao}', [NotificationAdminController::class, 'destroy']);
});

/*
|--------------------------------------------------------------------------
| Client Routes (Middleware: auth:sanctum)
|--------------------------------------------------------------------------
| Các route dành riêng cho Client App/Web (My Account section)
*/
Route::middleware('auth:sanctum')->group(function () {
    // Vé của tôi
    Route::get('/tickets', [TicketController::class, 'index']);
    Route::get('/tickets/latest', [TicketController::class, 'latest']); // Vé mới nhất (cho Home screen)

    // Lịch sử đơn hàng
    Route::get('/me/orders', [OrderClientController::class, 'index']);
    Route::get('/me/orders/{donHang}', [OrderClientController::class, 'show']);
    Route::post('/me/orders/{donHang}/cancel', [OrderClientController::class, 'cancel']); // Hủy đơn hàng

    // Đánh giá chuyến đi
    Route::get('/ratings/me', [RatingClientController::class, 'index']);
    Route::post('/ratings', [RatingClientController::class, 'store']);
});

// =========================================================
// ===================== END DKMN API =====================
// =========================================================