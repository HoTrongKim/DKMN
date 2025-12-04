<?php

namespace App\Http\Controllers;

use App\Models\Tram;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Controller quản lý trạm/bến (stations)
 * Lấy danh sách trạm, filter theo tỉnh/thành, search theo keyword
 * Hỗ trợ caching để tăng performance
 */
class TramController extends Controller
{
    /**
     * Lấy danh sách trạm có cache, filter theo tỉnh thành và keyword
     */
    /**
     * Lấy danh sách trạm có cache, filter theo tỉnh thành và keyword
     */
    public function getData(Request $request)
    {
        $query = Tram::query()->with('tinhThanh');
        
        // Chỉ cache khi không có keyword search để tránh cache key explosion
        $cacheable = !$request->filled('keyword');
        $cacheKey = 'tram_data'; // Base cache key

        // Filter theo tỉnh thành
        if ($request->filled('tinh_thanh_id')) {
            $query->where('tinh_thanh_id', (int) $request->input('tinh_thanh_id'));
            $cacheKey .= ':city_' . (int) $request->input('tinh_thanh_id');
        }

        // Filter theo loại trạm
        if ($request->filled('loai')) {
            $query->where('loai', $request->input('loai'));
            $cacheKey .= ':type_' . $request->input('loai');
        }

        // Search theo keyword (tên trạm)
        if ($request->filled('keyword')) {
            $keyword = trim($request->input('keyword'));
            $query->where('ten', 'like', "%{$keyword}%");
        }

        // Định nghĩa logic lấy dữ liệu để dùng cho cả cache và non-cache
        $resolver = function () use ($query) {
            return $query->orderBy('ten')->get()->map(function (Tram $tram) {
                return [
                    'id' => $tram->id,
                    'ten' => $tram->ten,
                    'loai' => $tram->loai,
                    'dia_chi' => $tram->dia_chi,
                    'tinh_thanh_id' => $tram->tinh_thanh_id,
                    'tinh_thanh' => $tram->tinhThanh->ten ?? null,
                ];
            });
        };

        if ($cacheable) {
            // Nếu không có filter gì đặc biệt thì dùng key all
            if ($cacheKey === 'tram_data') {
                $cacheKey = 'tram_data:all';
            }
            // Cache trong 5 phút (300s)
            $trams = Cache::remember($cacheKey, 300, $resolver);
        } else {
            // Không cache nếu đang search keyword
            $trams = $resolver();
        }

        // Trả về JSON response
        return response()->json(['data' => $trams]);
    }
}
