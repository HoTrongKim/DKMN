<?php

namespace App\Http\Controllers;

use App\Models\ThongBao;
use Illuminate\Http\Request;

/**
 * Controller quản lý thông báo (notifications) cho user
 * User xem thông báo của mình, mark as read
 * Inbox system với thông báo chia theo loại
 */
class ThongBaoController extends Controller
{
    /**
     * Lấy danh sách tất cả thông báo (admin/internal)
     */
    public function getData()
    {
        // Trả về JSON response
        return response()->json(['data' => ThongBao::orderByDesc('ngay_tao')->get()]);
    }

    /**
     * Lấy danh sách thông báo của user hiện tại
     * Bao gồm thông báo riêng (nguoi_dung_id = user_id) và thông báo chung (nguoi_dung_id = null)
     */
    public function me(Request $request)
    {
        $user = $request->user('sanctum') ?? $request->user();

        if (!$user) {
            // Trả về JSON response
        return response()->json([
                'message' => 'Unauthenticated',
            ], 401);
        }

        // Limit số lượng thông báo
        $limitInput = $request->input('limit', 20);
        $limit = is_numeric($limitInput) ? (int) $limitInput : 20;
        $limit = max(1, min(50, $limit));

        $notifications = ThongBao::query()
            ->where(function ($query) use ($user) {
                // Lấy thông báo chung hoặc thông báo riêng của user
                $query->whereNull('nguoi_dung_id')
                    ->orWhere('nguoi_dung_id', $user->id);
            })
            ->where(function ($query) use ($request) {
                // Filter theo loại nếu có
                $type = $request->input('type');
                if ($type) {
                    $query->where('loai', $type);
                }
            })
            ->orderByDesc('ngay_tao')
            ->limit($limit)
            ->get()
            ->map(fn (ThongBao $notification) => $this->transform($notification, $user->id))
            ->values();

        // Trả về JSON response
        return response()->json([
            'status' => true,
            'data' => $notifications,
        ]);
    }

    /**
     * Đánh dấu thông báo là đã đọc
     * Nhận vào mảng IDs hoặc đánh dấu tất cả nếu không gửi IDs (tùy logic, ở đây đang require IDs hoặc update all user's notif nếu logic mở rộng)
     * Hiện tại logic: update các ID được gửi thuộc về user
     */
    public function markAsRead(Request $request)
    {
        $user = $request->user('sanctum') ?? $request->user();

        if (!$user) {
            // Trả về JSON response
        return response()->json([
                'message' => 'Unauthenticated',
            ], 401);
        }

        // Parse và validate danh sách ID
        $ids = collect($request->input('ids', []))
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        // Update trạng thái da_doc = 1
        $query = ThongBao::query()->where('nguoi_dung_id', $user->id);
        if (!empty($ids)) {
            $query->whereIn('id', $ids);
        }

        $updated = $query->update(['da_doc' => 1]);

        // Trả về JSON response
        return response()->json([
            'status' => true,
            'data' => [
                'updated' => $updated,
            ],
        ]);
    }

    /**
     * Hộp thư đến (loại "inbox") cho người dùng
     */
    /**
     * Hộp thư đến (loại "inbox") cho người dùng
     * Chỉ lấy các thông báo loại INBOX
     */
    public function inbox(Request $request)
    {
        $user = $request->user('sanctum') ?? $request->user();

        if (!$user) {
            // Trả về JSON response
        return response()->json([
                'message' => 'Unauthenticated',
            ], 401);
        }

        $limitInput = $request->input('limit', 20);
        $limit = is_numeric($limitInput) ? (int) $limitInput : 20;
        $limit = max(1, min(50, $limit));

        $messages = ThongBao::query()
            ->where('loai', ThongBao::LOAI_INBOX)
            ->where(function ($query) use ($user) {
                $query->whereNull('nguoi_dung_id')
                    ->orWhere('nguoi_dung_id', $user->id);
            })
            ->orderByDesc('ngay_tao')
            ->limit($limit)
            ->get()
            ->map(fn (ThongBao $notification) => $this->transform($notification, $user->id))
            ->values();

        // Trả về JSON response
        return response()->json([
            'status' => true,
            'data' => $messages,
        ]);
    }

    /**
     * Đánh dấu các tin nhắn inbox là đã đọc
     */
    public function markInboxAsRead(Request $request)
    {
        $user = $request->user('sanctum') ?? $request->user();

        if (!$user) {
          // Trả về JSON response
        return response()->json([
              'message' => 'Unauthenticated',
          ], 401);
        }

        // Parse danh sách ID
        $ids = collect($request->input('ids', []))
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        // Update trạng thái
        $query = ThongBao::query()
            ->where('loai', ThongBao::LOAI_INBOX)
            ->where('nguoi_dung_id', $user->id);
        if (!empty($ids)) {
            $query->whereIn('id', $ids);
        }

        $updated = $query->update(['da_doc' => 1]);

        // Trả về JSON response
        return response()->json([
            'status' => true,
            'data' => [
                'updated' => $updated,
            ],
        ]);
    }

    /**
     * Transform model ThongBao sang format API response
     */
    private function transform(ThongBao $notification, ?int $userId = null): array
    {
        return [
            'id' => $notification->id,
            'userId' => $notification->nguoi_dung_id,
            'title' => $notification->tieu_de,
            'message' => $notification->noi_dung,
            'type' => $notification->loai,
            // Nếu là thông báo chung (userId null), coi như đã đọc nếu user đã xem (logic này có thể cần bảng phụ để track read status cho global notif)
            // Hiện tại logic tạm: global notif luôn coi là read hoặc unread tùy client xử lý, ở đây trả về raw
            'read' => (bool) $notification->da_doc || ($userId && $notification->nguoi_dung_id === null),
            'createdAt' => $notification->ngay_tao,
        ];
    }
}
