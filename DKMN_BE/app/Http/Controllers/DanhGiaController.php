<?php

namespace App\Http\Controllers;

use App\Models\DanhGia;
use Illuminate\Http\Request;

class DanhGiaController extends Controller
{
    public function getData(Request $request)
    {
        $limit = (int) $request->input('limit', 9);
        $limit = max(1, min(20, $limit));

        $ratings = DanhGia::query()
            ->with([
                'nguoiDung:id,ho_ten',
                'chuyenDi.tramDi:id,ten',
                'chuyenDi.tramDen:id,ten',
                'chuyenDi.nhaVanHanh:id,ten',
            ])
            ->where('trang_thai', 'chap_nhan')
            ->orderByDesc('ngay_tao')
            ->limit($limit)
            ->get()
            ->map(function (DanhGia $rating) {
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

        return response()->json(['data' => $ratings]);
    }
}


