<?php

namespace App\Http\Controllers;

use App\Models\NguoiDungQuyenHan;

/**
 * Controller quản lý quyền hạn của người dùng
 * CRUD đơn giản cho bảng nguoi_dung_quyen_hans (user permissions)
 */
class NguoiDungQuyenHanController extends Controller
{
    /**
     * Lấy danh sách tất cả user-permission mappings (admin/internal)
     */
    public function getData()
    {
        return response()->json(['data' => NguoiDungQuyenHan::orderByDesc('ngay_cap')->get()]);
    }
}


