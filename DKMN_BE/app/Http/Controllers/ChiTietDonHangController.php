<?php

namespace App\Http\Controllers;

use App\Models\ChiTietDonHang;
use Illuminate\Http\Request;

/**
 * Controller lấy chi tiết đơn hàng (thông tin hành khách)
 * Hỗ trợ filter theo donHangId, maDon, keyword (tên/sđt hành khách, số ghế)
 */
class ChiTietDonHangController extends Controller
{
    /**
     * Lấy danh sách chi tiết đơn hàng với eager loading
     * Eager load: donHang, ghe, chuyenDi để tránh N+1
     * Filter: donHangId, maDon, keyword (search tên/sđt hành khách, mã đơn, nơi đi/đến, số ghế)
     */
    public function getData(Request $request)
    {
        // Khởi tạo query từ model ChiTietDonHang
        $query = ChiTietDonHang::query()
            // Eager load các quan hệ để tối ưu hiệu suất
            ->with([
                // Lấy thông tin đơn hàng (chỉ lấy các cột cần thiết)
                'donHang:id,ma_don,noi_di,noi_den,tram_don,tram_tra,ten_khach,sdt_khach,ten_nha_van_hanh',
                // Lấy thông tin ghế
                'ghe:id,chuyen_di_id,so_ghe',
                // Lấy thông tin chuyến đi từ ghế
                'ghe.chuyenDi:id,tram_di_id,tram_den_id',
            ])
            // Sắp xếp mới nhất lên đầu
            ->orderByDesc('ngay_tao');

        // Lọc theo ID đơn hàng nếu có
        if ($request->filled('donHangId')) {
            $query->where('don_hang_id', (int) $request->input('donHangId'));
        }

        // Lọc theo mã đơn hàng nếu có
        if ($request->filled('maDon')) {
            $query->whereHas('donHang', function ($sub) use ($request) {
                $sub->where('ma_don', $request->input('maDon'));
            });
        }

        // Tìm kiếm theo từ khóa (tên, sđt, mã đơn, nơi đi/đến, số ghế)
        if ($request->filled('keyword')) {
            $keyword = trim($request->input('keyword'));
            $query->where(function ($sub) use ($keyword) {
                $sub->where('ten_hanh_khach', 'like', "%{$keyword}%") // Tìm theo tên hành khách
                    ->orWhere('sdt_hanh_khach', 'like', "%{$keyword}%") // Tìm theo SĐT hành khách
                    ->orWhereHas('donHang', function ($don) use ($keyword) {
                        // Tìm trong thông tin đơn hàng liên quan
                        $don->where('ma_don', 'like', "%{$keyword}%")
                            ->orWhere('noi_di', 'like', "%{$keyword}%")
                            ->orWhere('noi_den', 'like', "%{$keyword}%");
                    })
                    ->orWhereHas('ghe', function ($seat) use ($keyword) {
                        // Tìm theo số ghế
                        $seat->where('so_ghe', 'like', "%{$keyword}%");
                    });
            });
        }

        return response()->json([
            'data' => $query->get(),
        ]);
    }
}

