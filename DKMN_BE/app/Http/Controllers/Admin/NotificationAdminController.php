<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NguoiDung;
use App\Models\ThongBao;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

/**
 * Controller quản lý thông báo (notifications) cho Admin
 * Admin gửi thông báo tới khách hàng (bulk messaging)
 * Hỗ trợ gửi theo user IDs hoặc emails
 */
class NotificationAdminController extends Controller
{
    /**
     * Danh sách notification gần đây (limit tối đa 100)
     * Eager load: nguoiDung
     */
    /**
     * Danh sách notification gần đây (limit tối đa 100)
     * Eager load: nguoiDung
     */
    public function index(Request $request)
    {
        // Lấy tham số limit từ request, mặc định là 20
        // Giới hạn limit trong khoảng [1, 100] để tránh query quá nặng
        $limitInput = $request->input('limit', 20);
        $limit = is_numeric($limitInput) ? (int) $limitInput : 20;
        $limit = max(1, min(100, $limit));

        // Query lấy danh sách thông báo
        // Eager load 'nguoiDung' để lấy thông tin người nhận (tránh N+1)
        // Sắp xếp giảm dần theo ngày tạo (mới nhất lên đầu)
        $notifications = ThongBao::query()
            ->with(['nguoiDung'])
            ->orderByDesc('ngay_tao')
            ->limit($limit)
            ->get()
            ->map(fn (ThongBao $notification) => $this->transform($notification))
            ->values();

        // Trả về response JSON chuẩn
        return response()->json([
            'status' => true,
            'data' => $notifications,
            'meta' => [
                'count' => $notifications->count(),
            ],
        ]);
    }

    /**
     * Tạo thông báo mới gửi tới khách hàng (bulk insert)
     * Hỗ trợ gửi theo recipientIds hoặc recipientEmails
     * Chỉ gửi cho user có vai_tro != 'quan_tri'
     * Logic:
     * - Validate input (title, message, type, recipients)
     * - Lọc danh sách user nhận (loại bỏ admin, trùng lặp)
     * - Bulk insert vào bảng thong_baos để tối ưu hiệu suất
     */
        /**
     * Tạo mới bản ghi
     */
    public function store(Request $request)
    {
        // Validate dữ liệu đầu vào
        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'message' => 'required|string|max:2000',
            'type' => [
                'nullable',
                'string',
                // Chỉ chấp nhận các loại thông báo hợp lệ
                Rule::in(['info', 'warning', 'success', 'error', ThongBao::LOAI_TRIP_UPDATE, ThongBao::LOAI_INBOX]),
            ],
            'recipientIds' => 'nullable|array',
            'recipientIds.*' => 'integer|exists:nguoi_dungs,id', // ID phải tồn tại trong bảng users
            'recipientEmails' => 'nullable|array',
            'recipientEmails.*' => 'email', // Email phải đúng định dạng
        ]);

        $type = $validated['type'] ?? 'info';
        // Lọc và loại bỏ các giá trị trùng lặp trong danh sách người nhận
        $recipientIds = collect($validated['recipientIds'] ?? [])->filter()->unique()->values();
        $recipientEmails = collect($validated['recipientEmails'] ?? [])->filter()->unique()->values();

        // Xây dựng query để lấy danh sách user cần gửi
        // Loại bỏ user có vai trò 'quan_tri' (admin không gửi thông báo cho chính mình qua API này)
        $usersQuery = NguoiDung::query()
            ->where('vai_tro', '!=', 'quan_tri');

        // Thêm điều kiện lọc theo ID nếu có
        if ($recipientIds->isNotEmpty()) {
            $usersQuery->whereIn('id', $recipientIds->all());
        }

        // Thêm điều kiện lọc theo Email nếu có
        if ($recipientEmails->isNotEmpty()) {
            $usersQuery->whereIn('email', $recipientEmails->all());
        }

        // Thực thi query và đảm bảo danh sách user là unique theo ID
        $users = $usersQuery->get()->unique('id');

        // Nếu không tìm thấy user nào phù hợp thì trả về lỗi 422
        if ($users->isEmpty()) {
            // Trả về JSON response
        return response()->json([
                'status' => false,
                'message' => 'Không tìm thấy khách hàng phù hợp để gửi thông báo.',
            ], 422);
        }

        $now = Carbon::now();
        // Chuẩn bị dữ liệu để bulk insert (chèn nhiều dòng 1 lúc)
        // Cách này nhanh hơn nhiều so với việc loop và create từng record
        $rows = $users->map(function (NguoiDung $user) use ($type, $validated, $now) {
            return [
                'nguoi_dung_id' => $user->id,
                'tieu_de' => $validated['title'],
                'noi_dung' => $validated['message'],
                'loai' => $type,
                'da_doc' => 0, // Mặc định là chưa đọc
                'ngay_tao' => $now,
            ];
        })->toArray();

        // Thực hiện insert vào DB
        ThongBao::insert($rows);

        // Trả về JSON response
        return response()->json([
            'status' => true,
            'message' => 'Đã gửi thông báo đến khách hàng.',
            'data' => [
                'recipients' => count($rows), // Trả về số lượng người nhận thực tế
                'type' => $type,
            ],
        ], 201);
    }

    /**
     * Xóa notification
     */
        /**
     * Xóa bản ghi theo ID
     */
    public function destroy(ThongBao $thongBao)
    {
        // Xóa cứng record khỏi DB
        $thongBao->delete();

        // Trả về JSON response
        return response()->json([
            'status' => true,
            'message' => 'Đã xóa thông báo.',
        ]);
    }

    /**
     * Transform notification object sang format API response
     */
    private function transform(ThongBao $notification): array
    {
        return [
            'id' => $notification->id,
            'userId' => $notification->nguoi_dung_id,
            // Lấy tên người nhận, fallback qua nhiều trường khác nhau nếu null
            'recipient' => $notification->nguoiDung?->ho_va_ten
                ?? $notification->nguoiDung?->ho_ten
                ?? $notification->nguoiDung?->name
                ?? $notification->nguoiDung?->email,
            'title' => $notification->tieu_de,
            'message' => $notification->noi_dung,
            'type' => $notification->loai,
            'read' => (bool) $notification->da_doc,
            'createdAt' => $notification->ngay_tao,
        ];
    }
}
