<?php

namespace App\Http\Controllers;

use App\Http\Resources\TripResource;
use App\Models\ChuyenDi;
use App\Models\Ghe;
use App\Services\TripSeatSynchronizer;
use Illuminate\Support\Collection;

/**
 * Controller quản lý ghế (seats) cho chuyến đi
 * Lấy danh sách ghế theo chuyến, sync seat status
 * Transform seat data sang format API (code, status, price)
 */
class GheController extends Controller
{
    /**
     * Lấy danh sách tất cả ghế (admin/internal)
     */
    public function getData()
    {
        // Trả về JSON response
        return response()->json(['data' => Ghe::orderByDesc('ngay_tao')->get()]);
    }
    public function getByChuyenDi(ChuyenDi $chuyenDi)
    {
        // Đồng bộ trạng thái ghế mới nhất (nếu cần)
        TripSeatSynchronizer::sync($chuyenDi);
        // Eager load các quan hệ
        $chuyenDi->loadMissing(['ghes', 'nhaVanHanh', 'tramDi.tinhThanh', 'tramDen.tinhThanh']);

        // Map danh sách ghế sang format API
        $seats = $chuyenDi->ghes
            ->sortBy('so_ghe', SORT_NATURAL) // Sắp xếp theo số ghế tự nhiên (1, 2, 10 thay vì 1, 10, 2)
            ->values()
            ->map(function (Ghe $ghe) {
                return [
                    'id' => $ghe->id,
                    'code' => $ghe->so_ghe,
                    'label' => $ghe->so_ghe,
                    'type' => $ghe->loai_ghe,
                    'price' => (float) $ghe->gia,
                    'status' => $ghe->trang_thai,
                    'available' => $ghe->trang_thai === 'trong',
                    'booked' => $ghe->trang_thai === 'da_dat',
                    'unavailable' => $ghe->trang_thai === 'khoa',
                ];
            });

        // Xây dựng layout hiển thị ghế (ma trận)
        $seatLayout = $this->buildSeatLayout($seats, $chuyenDi);

        // Trả về JSON response
        return response()->json([
            'status' => true,
            'data' => [
                'trip' => TripResource::make($chuyenDi),
                'seats' => $seats->values(),
                'layout' => $seatLayout,
                'stats' => [
                    'total' => $seats->count(),
                    'available' => $seats->where('available', true)->count(),
                    'booked' => $seats->where('booked', true)->count(),
                ],
            ],
        ]);
    }

    private function buildSeatLayout(Collection $seats, ChuyenDi $chuyenDi): array
    {
        $vehicleKey = match ($chuyenDi->nhaVanHanh->loai ?? null) {
            'tau_hoa' => 'train',
            'may_bay' => 'plane',
            default => 'bus',
        };

        $chunkSize = match ($vehicleKey) {
            'plane' => 6,
            'train' => 8,
            default => 4,
        };

        return $seats
            ->chunk($chunkSize)
            ->map(fn ($chunk) => $chunk->map(function ($seat) {
                return [
                    'id' => $seat['id'],
                    'label' => $seat['label'],
                    'available' => $seat['available'],
                    'booked' => $seat['booked'],
                    'unavailable' => $seat['unavailable'],
                ];
            })->values())
            ->values()
            ->toArray();
    }
}
