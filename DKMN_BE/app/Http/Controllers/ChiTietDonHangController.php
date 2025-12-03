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
        $query = ChiTietDonHang::query()
            ->with([
                'donHang:id,ma_don,noi_di,noi_den,tram_don,tram_tra,ten_khach,sdt_khach,ten_nha_van_hanh',
                'ghe:id,chuyen_di_id,so_ghe',
                'ghe.chuyenDi:id,tram_di_id,tram_den_id',
            ])
            ->orderByDesc('ngay_tao');

        if ($request->filled('donHangId')) {
            $query->where('don_hang_id', (int) $request->input('donHangId'));
        }

        if ($request->filled('maDon')) {
            $query->whereHas('donHang', function ($sub) use ($request) {
                $sub->where('ma_don', $request->input('maDon'));
            });
        }

        if ($request->filled('keyword')) {
            $keyword = trim($request->input('keyword'));
            $query->where(function ($sub) use ($keyword) {
                $sub->where('ten_hanh_khach', 'like', "%{$keyword}%")
                    ->orWhere('sdt_hanh_khach', 'like', "%{$keyword}%")
                    ->orWhereHas('donHang', function ($don) use ($keyword) {
                        $don->where('ma_don', 'like', "%{$keyword}%")
                            ->orWhere('noi_di', 'like', "%{$keyword}%")
                            ->orWhere('noi_den', 'like', "%{$keyword}%");
                    })
                    ->orWhereHas('ghe', function ($seat) use ($keyword) {
                        $seat->where('so_ghe', 'like', "%{$keyword}%");
                    });
            });
        }

        return response()->json([
            'data' => $query->get(),
        ]);
    }
}

