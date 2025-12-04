<?php

namespace App\Http\Controllers;

use App\Models\HuyVe;

/**
 * Controller lấy danh sách vé đã hủy
 * Dùng cho admin xem lịch sử hủy vé
 */
class HuyVeController extends Controller
{
    /**
     * Lấy tất cả records hủy vé, sắp xếp mới nhất trước
     */
    public function getData()
    {
        // Trả về danh sách hủy vé, mới nhất lên đầu
        return response()->json(['data' => HuyVe::orderByDesc('ngay_huy')->get()]);
    }
}


