<?php

namespace App\Http\Controllers;

use App\Models\CauHinhHeThong;
use Illuminate\Support\Facades\Cache;

/**
 * Controller lấy cấu hình hệ thống (cached 15 phút)
 * Dùng để lấy các settings như banner, contact info, system params
 */
class CauHinhHeThongController extends Controller
{
    /**
     * Lấy toàn bộ cấu hình hệ thống
     * Kết quả được cache 15 phút (900s) để tránh query DB nhiều lần
     */
    public function getData()
    {
        $data = Cache::remember('cau_hinh_he_thong_all_v1', 900, function () {
            return CauHinhHeThong::orderBy('khoa')->get();
        });

        return response()->json(['data' => $data]);
    }
}

