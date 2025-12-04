<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\DanhGia;
use App\Models\DonHang;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller quản lý đánh giá (ratings) cho khách hàng
 * Xem lịch sử đánh giá, gửi đánh giá mới cho chuyến đã hoàn thành
 */
class RatingClientController extends Controller
{
    /**
     * Danh sách đánh giá của user hiện tại
     * Optional filter: tripId
     */
    /**
     * Danh sách đánh giá của user hiện tại
     * Optional filter: tripId
     * Logic:
     * - Lấy user hiện tại
     * - Query bảng danh_gias theo user_id
     * - Nếu có tripId thì lọc thêm theo chuyến đi
     * - Trả về danh sách đã được map format
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user('sanctum') ?? $request->user();

        $ratings = DanhGia::query()
            ->where('nguoi_dung_id', $user->id) // Chỉ lấy đánh giá của user này
            ->when(
                $request->integer('tripId'),
                fn ($query, $tripId) => $query->where('chuyen_di_id', $tripId) // Lọc theo chuyến đi nếu có
            )
            ->orderByDesc('ngay_tao') // Mới nhất lên đầu
            ->get()
            ->map(function (DanhGia $rating) {
                return [
                    'id' => $rating->id,
                    'tripId' => $rating->chuyen_di_id,
                    'orderId' => $rating->don_hang_id,
                    'rating' => $rating->diem,
                    'comment' => $rating->nhan_xet,
                    'status' => $rating->trang_thai,
                    'createdAt' => optional($rating->ngay_tao)->toIso8601String(),
                ];
            });

        // Trả về JSON response
        return response()->json([
            'status' => true,
            'data' => $ratings,
        ]);
    }

    /**
     * Gửi đánh giá mới cho chuyến đã hoàn thành
     * Validate: user phải có đơn hàng (status: hoan_tat hoặc da_xac_nhan), chưa đánh giá trước đó
     * Rating từ 1-5 sao, comment tối đa 500 ký tự
     * Status mặc định: cho_duyet (chờ admin duyệt)
     */
        /**
     * Tạo mới bản ghi
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user('sanctum') ?? $request->user();

        // Validate input
        $validated = $request->validate([
            'tripId' => 'required|integer|exists:chuyen_dis,id',
            'rating' => 'required|integer|min:1|max:5', // Số sao từ 1 đến 5
            'comment' => 'nullable|string|max:500', // Nhận xét tùy chọn
        ]);

        // 1. Tìm đơn hàng hợp lệ của user cho chuyến đi này
        // Đơn hàng phải là của user và thuộc chuyến đi được chọn
        $order = DonHang::query()
            ->where('nguoi_dung_id', $user->id)
            ->where('chuyen_di_id', $validated['tripId'])
            ->orderByDesc('ngay_tao') // Lấy đơn mới nhất nếu có nhiều đơn
            ->first();

        if (!$order) {
            // Trả về JSON response
        return response()->json([
                'status' => false,
                'message' => 'Không tìm thấy đơn hàng hợp lệ cho chuyến đi này.',
            ], 422);
        }

        // 2. Kiểm tra trạng thái đơn hàng
        // Chỉ cho phép đánh giá khi đơn hàng đã hoàn tất hoặc đã xác nhận (đã đi)
        if (!in_array($order->trang_thai, ['hoan_tat', 'da_xac_nhan'])) {
            // Trả về JSON response
        return response()->json([
                'status' => false,
                'message' => 'Đơn hàng chưa hoàn tất, không thể đánh giá.',
            ], 422);
        }

        // 3. Kiểm tra xem đã đánh giá chưa (mỗi đơn/chuyến chỉ được đánh giá 1 lần)
        $existing = DanhGia::query()
            ->where('nguoi_dung_id', $user->id)
            ->where('chuyen_di_id', $validated['tripId'])
            ->where('don_hang_id', $order->id)
            ->first();

        if ($existing) {
            // Trả về JSON response
        return response()->json([
                'status' => false,
                'message' => 'Bạn đã đánh giá chuyến đi này.',
            ], 409);
        }

        // 4. Tạo đánh giá mới
        $rating = DanhGia::create([
            'nguoi_dung_id' => $user->id,
            'chuyen_di_id' => $validated['tripId'],
            'don_hang_id' => $order->id,
            'diem' => $validated['rating'],
            'nhan_xet' => $validated['comment'] ?? null,
            'trang_thai' => 'cho_duyet', // Mặc định chờ duyệt
            'ngay_tao' => now(),
        ]);

        // Trả về JSON response
        return response()->json([
            'status' => true,
            'message' => 'Đã ghi nhận đánh giá. Hệ thống sẽ duyệt trong thời gian sớm nhất.',
            'data' => [
                'id' => $rating->id,
                'tripId' => $rating->chuyen_di_id,
                'orderId' => $rating->don_hang_id,
                'rating' => $rating->diem,
                'comment' => $rating->nhan_xet,
                'status' => $rating->trang_thai,
                'createdAt' => optional($rating->ngay_tao)->toIso8601String(),
            ],
        ], 201);
    }
}
