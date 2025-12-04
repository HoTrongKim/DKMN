<?php

namespace App\Http\Controllers;

use App\Models\NhaVanHanh;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Controller quản lý nhà vận hành (carriers/operators)
 * Lấy danh sách nhà vận hành, filter theo loại (bus/train/plane)
 * Hỗ trợ search và caching
 */
class NhaVanHanhController extends Controller
{
    /**
     * Lấy danh sách nhà vận hành có cache, filter theo loại và keyword
     */
    public function getData(Request $request)
    {
        $query = NhaVanHanh::query();
        $cacheable = !$request->filled('keyword');
        $cacheKeyParts = ['nhavanhanh'];

        if ($request->filled('loai')) {
            $query->where('loai', $request->input('loai'));
            $cacheKeyParts[] = 'type_' . $request->input('loai');
        }

        if ($request->filled('trang_thai')) {
            $query->where('trang_thai', $request->input('trang_thai'));
            $cacheKeyParts[] = 'status_' . $request->input('trang_thai');
        }

        if ($request->filled('keyword')) {
            $keyword = trim($request->input('keyword'));
            $query->where('ten', 'like', "%{$keyword}%");
        }

        $resolver = fn () => $query->orderBy('ten')->get();
        $data = $cacheable
            ? Cache::remember(implode(':', $cacheKeyParts), 300, $resolver)
            : $resolver();

        // Trả về JSON response
        return response()->json(['data' => $data]);
    }
}
