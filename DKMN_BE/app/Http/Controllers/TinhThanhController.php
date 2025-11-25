<?php

namespace App\Http\Controllers;

use App\Models\TinhThanh;
use Illuminate\Support\Facades\Cache;

class TinhThanhController extends Controller
{
    public function getData()
    {
        $data = Cache::remember('tinh_thanh_all_v1', 3600, function () {
            return TinhThanh::orderBy('ten')->get();
        });

        return response()->json(['data' => $data]);
    }
}

