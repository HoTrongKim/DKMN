<?php

namespace App\Http\Controllers;

use App\Models\PhiDichVu;

/**
 * Controller quản lý phí dịch vụ (service fees)
 * CRUD đơn giản cho bảng phi_dich_vus (các loại phí: bảo hiểm, phụ phí, v.v.)
 */
class PhiDichVuController extends Controller
{
    /**
     * Lấy danh sách tất cả phí dịch vụ sắp xếp theo tên (admin/internal)
     */
    public function getData()
    {
        // Lấy danh sách các loại phí dịch vụ, sắp xếp theo tên
        // Dùng để hiển thị cấu hình phí cho admin
        return response()->json(['data' => PhiDichVu::orderBy('ten')->get()]);
    }
}


