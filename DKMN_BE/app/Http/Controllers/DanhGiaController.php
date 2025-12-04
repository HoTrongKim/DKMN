<?php

namespace App\Http\Controllers;

use App\Models\DanhGia;
use Illuminate\Http\Request;

/**
 * Controller lấy danh sách đánh giá đã được chấp nhận
 * Dùng để hiển thị reviews trên trang chủ/public
 */
class DanhGiaController extends Controller
{
    /**
     * Lấy danh sách đánh giá đã duyệt (trang_thai = 'chap_nhan')
     * Limit tối đa 20 records, default 9
     * Transform sang format gọn với trip info, customer name, rating
     */
    public function getData(Request $request)
    {
        // Lấy limit từ request, giới hạn từ 1 đến 20
        $limit = (int) $request->input('limit', 9);
        $limit = max(1, min(20, $limit));

        // Query lấy danh sách đánh giá
        $ratings = DanhGia::query()
            ->with([
                'nguoiDung:id,ho_ten', // Lấy tên người dùng
                'chuyenDi.tramDi:id,ten', // Lấy tên trạm đi
                'chuyenDi.tramDen:id,ten', // Lấy tên trạm đến
                'chuyenDi.nhaVanHanh:id,ten', // Lấy tên nhà vận hành
            ])
            ->where('trang_thai', 'chap_nhan') // Chỉ lấy đánh giá đã được duyệt
            ->orderByDesc('ngay_tao') // Mới nhất trước
            ->limit($limit)
            ->get()
            ->map(function (DanhGia $rating) {
                // Transform data để trả về format gọn gàng
                $trip = $rating->chuyenDi;
                $from = $trip?->tramDi?->ten;
                $to = $trip?->tramDen?->ten;

                return [
                    'id' => $rating->id,
                    'rating' => (float) $rating->diem,
                    'comment' => $rating->nhan_xet,
                    'customer' => $rating->nguoiDung?->ho_ten ?? 'Khách hàng',
                    'trip' => $from && $to ? "{$from} → {$to}" : null,
                    'tripId' => $rating->chuyen_di_id,
                    'operator' => $trip?->nhaVanHanh?->ten,
                    'createdAt' => optional($rating->ngay_tao)->toIso8601String(),
                ];
            });

        // Trả về JSON response
        return response()->json(['data' => $ratings]);
    }
}


