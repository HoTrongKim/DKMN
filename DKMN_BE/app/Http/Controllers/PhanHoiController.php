<?php

namespace App\Http\Controllers;

use App\Models\PhanHoi;

/**
 * Controller quản lý phản hồi/feedback từ khách hàng
 * CRUD đơn giản cho bảng phan_hois (customer feedback)
 */
class PhanHoiController extends Controller
{
    /**
     * Lấy danh sách tất cả phản hồi (admin/internal)
     */
    public function getData()
    {
        // Lấy danh sách phản hồi từ khách hàng, mới nhất lên đầu
        // Dùng cho admin xử lý feedback
        return response()->json(['data' => PhanHoi::orderByDesc('ngay_tao')->get()]);
    }
}


