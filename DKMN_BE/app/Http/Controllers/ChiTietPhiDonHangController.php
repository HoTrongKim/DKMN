<?php

namespace App\Http\Controllers;

use App\Models\ChiTietPhiDonHang;

/**
 * Controller quản lý chi tiết phí đơn hàng
 * CRUD đơn giản cho bảng chi_tiet_phi_don_hangs
 */
class ChiTietPhiDonHangController extends Controller
{
    /**
     * Lấy danh sách tất cả chi tiết phí (admin/internal)
     */
    public function getData()
    {
        // Trả về danh sách chi tiết phí, sắp xếp theo thời gian tạo giảm dần
        return response()->json(['data' => ChiTietPhiDonHang::orderByDesc('ngay_tao')->get()]);
    }
}


