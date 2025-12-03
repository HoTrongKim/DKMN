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
    public function getData()
    {
        $data = Cache::remember('tinh_thanh_all_v1', 3600, function () {
            return TinhThanh::orderBy('ten')->get();
        });

        return response()->json(['data' => $data]);
    }
}

