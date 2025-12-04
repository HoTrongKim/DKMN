<?php

namespace App\Http\Controllers;

use App\Models\TinhThanh;
use Illuminate\Support\Facades\Cache;

/**
 * Controller quản lý tỉnh/thành phố
 * Lấy danh sách tất cả tỉnh thành với cache 1 giờ
 */
class TinhThanhController extends Controller
{
    /**
     * Lấy danh sách tỉnh thành có cache (1 giờ)
     */
    /**
     * Lấy danh sách tỉnh thành
     * Sử dụng Cache để giảm tải database vì dữ liệu tỉnh thành ít thay đổi
     * Cache key: tinh_thanh_all_v1
     * Thời gian cache: 3600 giây (1 giờ)
     */
    public function getData()
    {
        $data = Cache::remember('tinh_thanh_all_v1', 3600, function () {
            // Lấy tất cả và sắp xếp theo tên
            return TinhThanh::orderBy('ten')->get();
        });

        // Trả về JSON response
        return response()->json(['data' => $data]);
    }
}

