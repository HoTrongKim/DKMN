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
        return response()->json(['data' => PhanHoi::orderByDesc('ngay_tao')->get()]);
    }
}


