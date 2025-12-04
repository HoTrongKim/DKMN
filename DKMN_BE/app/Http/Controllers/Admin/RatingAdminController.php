<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DanhGia;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Controller quản lý đánh giá (ratings/reviews) cho Admin
 * Xem danh sách, duyệt (approve/reject), xóa đánh giá của khách hàng
 */
class RatingAdminController extends Controller
{
    /**
     * Danh sách đánh giá có filter: rating (1-5), status, search (theo tên khách/tuyến)
     * Eager load: nguoiDung, chuyenDi.tramDi, chuyenDi.tramDen
     * Logic:
     * - Lọc theo số sao (rating) và trạng thái duyệt
     * - Tìm kiếm từ khóa trong tên khách hàng, tên trạm đi/đến hoặc nội dung nhận xét
     * - Trả về dữ liệu đã được transform kèm thông tin chuyến đi
     */
    /**
     * Danh sách đánh giá có filter: rating (1-5), status, search (theo tên khách/tuyến)
     * Eager load: nguoiDung, chuyenDi.tramDi, chuyenDi.tramDen
     * Logic:
     * - Lọc theo số sao (rating) và trạng thái duyệt
     * - Tìm kiếm từ khóa trong tên khách hàng, tên trạm đi/đến hoặc nội dung nhận xét
     * - Trả về dữ liệu đã được transform kèm thông tin chuyến đi
     */
        /**
     * Lấy danh sách dữ liệu với phân trang và filter
     */
    public function index(Request $request): JsonResponse
    {
        // Validate các tham số lọc
        $validated = $request->validate([
            'rating' => 'nullable|integer|min:1|max:5', // Lọc theo số sao
            'status' => ['nullable', Rule::in(['cho_duyet', 'chap_nhan', 'tu_choi'])], // Lọc theo trạng thái duyệt
            'search' => 'nullable|string|max:150', // Từ khóa tìm kiếm
        ]);

        // Eager load các quan hệ để hiển thị thông tin chi tiết
        // nguoiDung: hiển thị tên người đánh giá
        // chuyenDi.tramDi/tramDen: hiển thị tên tuyến đường
        $query = DanhGia::query()
            ->with(['nguoiDung', 'chuyenDi.tramDi', 'chuyenDi.tramDen'])
            ->orderByDesc('ngay_tao'); // Mới nhất lên đầu

        // Áp dụng filter số sao
        if (!empty($validated['rating'])) {
            $query->where('diem', $validated['rating']);
        }

        // Áp dụng filter trạng thái
        if (!empty($validated['status'])) {
            $query->where('trang_thai', $validated['status']);
        }

        // Áp dụng tìm kiếm từ khóa
        if (!empty($validated['search'])) {
            $keyword = strtolower(trim($validated['search']));
            // Tìm kiếm trong nhiều trường liên quan bằng closure (nhóm điều kiện OR)
            $query->where(function ($sub) use ($keyword) {
                // Tìm trong tên người dùng
                $sub->whereHas('nguoiDung', fn ($q) => $q->where('ho_ten', 'like', "%{$keyword}%"))
                    // Tìm trong tên trạm đi
                    ->orWhereHas('chuyenDi.tramDi', fn ($q) => $q->where('ten', 'like', "%{$keyword}%"))
                    // Tìm trong tên trạm đến
                    ->orWhereHas('chuyenDi.tramDen', fn ($q) => $q->where('ten', 'like', "%{$keyword}%"))
                    // Tìm trong nội dung nhận xét
                    ->orWhere('nhan_xet', 'like', "%{$keyword}%");
            });
        }

        // Phân trang và transform dữ liệu
        $paginator = $query->paginate($this->resolvePerPage($request));
        $data = $paginator->getCollection()->map(function (DanhGia $rating) {
            $trip = $rating->chuyenDi;
            $from = $trip?->tramDi?->ten;
            $to = $trip?->tramDen?->ten;

            return [
                'id' => $rating->id,
                'rating' => $rating->diem,
                'comment' => $rating->nhan_xet,
                'status' => $rating->trang_thai,
                'customer' => $rating->nguoiDung?->ho_ten ?? 'Khách',
                'trip' => $from && $to ? "{$from} → {$to}" : null, // Format tên tuyến đường
                'tripId' => $rating->chuyen_di_id,
                'createdAt' => optional($rating->ngay_tao)->toIso8601String(),
            ];
        });

        return $this->respondWithPagination($paginator, $data);
    }

    /**
     * Cập nhật trạng thái đánh giá (cho_duyet/chap_nhan/tu_choi)
     * Admin duyệt hoặc từ chối review của khách
     */
        /**
     * Cập nhật bản ghi theo ID
     */
    public function update(Request $request, DanhGia $danhGia): JsonResponse
    {
        $validated = // Validate dữ liệu từ request
        $request->validate([
            'status' => ['required', Rule::in(['cho_duyet', 'chap_nhan', 'tu_choi'])],
        ]);

        // Cập nhật trạng thái mới
        $danhGia->update([
            'trang_thai' => $validated['status'],
        ]);

        // Trả về JSON response
        return response()->json([
            'status' => true,
            'message' => 'Đã cập nhật trạng thái đánh giá.',
        ]);
    }

    /**
     * Xóa đánh giá (admin có quyền xóa review không phù hợp)
     */
        /**
     * Xóa bản ghi theo ID
     */
    public function destroy(DanhGia $danhGia): JsonResponse
    {
        // Xóa cứng record khỏi DB
        $danhGia->delete();

        // Trả về JSON response
        return response()->json([
            'status' => true,
            'message' => 'Đã xóa đánh giá.',
        ]);
    }
}
