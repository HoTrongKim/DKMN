<?php

namespace App\Http\Controllers;

use App\Models\QuyenHan;

/**
 * Controller quản lý quyền hạn (permissions/roles)
 * CRUD đơn giản cho bảng quyen_hans (danh mục các quyền trong hệ thống)
 */
class QuyenHanController extends Controller
{
    /**
     * Lấy danh sách tất cả quyền hạn sắp xếp theo tên (admin/internal)
     */
    public function getData()
    {
        // Lấy danh sách các quyền hạn trong hệ thống, sắp xếp theo tên
        // Dùng để gán quyền cho user
        return response()->json(['data' => QuyenHan::orderBy('ten')->get()]);
    }
}


