<?php

namespace App\Http\Controllers;

use App\Models\CauHinhHeThong;
use Illuminate\Support\Facades\Cache;

class CauHinhHeThongController extends Controller
{
    public function getData()
    {
        $data = Cache::remember('cau_hinh_he_thong_all_v1', 900, function () {
            return CauHinhHeThong::orderBy('khoa')->get();
        });

        return response()->json(['data' => $data]);
    }
}

