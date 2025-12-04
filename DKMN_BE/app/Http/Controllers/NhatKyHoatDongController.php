<?php

namespace App\Http\Controllers;

use App\Models\NhatKyHoatDong;
use Illuminate\Http\Request;

/**
 * Controller quản lý nhật ký hoạt động (activity logs)
 * Lấy logs theo user, action, date range
 * Dùng để audit trail và tracking user activities
 */
class NhatKyHoatDongController extends Controller
{
    /**
     * Lấy danh sách nhật ký hoạt động có filter:
     * - nguoi_dung_id: Lọc theo user cụ thể
     * - me: Lấy logs của user hiện tại
     * - hanh_dong: Lọc theo loại action
     * - tu_ngay, den_ngay: Lọc theo date range
     */
    public function getData(Request $request)
    {
        // Khởi tạo query, sắp xếp mới nhất trước
        $query = NhatKyHoatDong::query()->orderByDesc('ngay_tao');

        // Filter theo ID người dùng cụ thể
        if ($request->filled('nguoi_dung_id')) {
            $query->where('nguoi_dung_id', (int) $request->input('nguoi_dung_id'));
        } 
        // Hoặc lấy logs của chính user đang đăng nhập (nếu có tham số 'me')
        elseif ($request->boolean('me') && $request->user()) {
            $query->where('nguoi_dung_id', $request->user()->id);
        }

        // Filter theo loại hành động (ví dụ: 'login', 'dat_ve', 'huy_ve')
        if ($request->filled('hanh_dong')) {
            $query->where('hanh_dong', $request->input('hanh_dong'));
        }

        // Filter theo khoảng thời gian (từ ngày)
        if ($request->filled('tu_ngay')) {
            $query->whereDate('ngay_tao', '>=', $request->input('tu_ngay'));
        }

        // Filter theo khoảng thời gian (đến ngày)
        if ($request->filled('den_ngay')) {
            $query->whereDate('ngay_tao', '<=', $request->input('den_ngay'));
        }

        return response()->json(['data' => $query->get()]);
    }
}

