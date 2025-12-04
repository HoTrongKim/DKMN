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
        // Khởi tạo query từ model NhaVanHanh
        $query = NhaVanHanh::query();
        
        // Xác định xem có thể cache kết quả hay không
        // Chỉ cache khi không có keyword search để tránh cache key explosion
        $cacheable = !$request->filled('keyword');
        $cacheKeyParts = ['nhavanhanh'];

        // Filter theo loại phương tiện (bus, train, plane)
        if ($request->filled('loai')) {
            $query->where('loai', $request->input('loai'));
            $cacheKeyParts[] = 'type_' . $request->input('loai');
        }

        // Filter theo trạng thái hoạt động
        if ($request->filled('trang_thai')) {
            $query->where('trang_thai', $request->input('trang_thai'));
            $cacheKeyParts[] = 'status_' . $request->input('trang_thai');
        }

        // Tìm kiếm theo tên nhà vận hành
        if ($request->filled('keyword')) {
            $keyword = trim($request->input('keyword'));
            $query->where('ten', 'like', "%{$keyword}%");
        }

        // Định nghĩa hàm lấy dữ liệu từ DB
        $resolver = fn () => $query->orderBy('ten')->get();
        
        // Nếu đủ điều kiện cache, dùng Cache::remember với key được build động
        // Cache trong 300 giây (5 phút)
        $data = $cacheable
            ? Cache::remember(implode(':', $cacheKeyParts), 300, $resolver)
            : $resolver();

        // Trả về JSON response
        return response()->json(['data' => $data]);
    }
}
