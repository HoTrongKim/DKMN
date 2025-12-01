<?php

namespace App\Http\Controllers;

use App\Models\LienHe;
use App\Models\NguoiDung;
use App\Models\ThongBao;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LienHeController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ho_ten' => 'required|string|max:100',
            'email' => 'required|string|email|max:150',
            'so_dien_thoai' => 'nullable|string|max:20',
            'chu_de' => 'required|string|max:100',
            'noi_dung' => 'required|string|max:5000',
        ]);

        $lienHe = LienHe::create([
            'ho_ten' => $validated['ho_ten'],
            'email' => $validated['email'],
            'so_dien_thoai' => $validated['so_dien_thoai'] ?? null,
            'chu_de' => $validated['chu_de'],
            'noi_dung' => $validated['noi_dung'],
            'trang_thai' => 'moi',
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Cảm ơn bạn đã liên hệ! Chúng tôi sẽ phản hồi trong thời gian sớm nhất.',
            'data' => $lienHe,
        ], 201);
    }

    public function getData(Request $request): JsonResponse
    {
        $query = LienHe::query()
            ->orderByDesc('ngay_tao');

        // Filter by status
        if ($request->has('trang_thai') && $request->trang_thai !== '') {
            $query->where('trang_thai', $request->trang_thai);
        }

        // Search
        if ($request->has('keyword') && $request->keyword !== '') {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('ho_ten', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%")
                    ->orWhere('chu_de', 'like', "%{$keyword}%")
                    ->orWhere('noi_dung', 'like', "%{$keyword}%");
            });
        }

        $lienHes = $query->get();

        return response()->json([
            'status' => true,
            'data' => $lienHes,
        ]);
    }

    public function update(Request $request, LienHe $lienHe): JsonResponse
    {
        $validated = $request->validate([
            'trang_thai' => 'required|string|in:moi,dang_xu_ly,da_tra_loi,dong',
            'tra_loi' => 'nullable|string|max:5000',
        ]);

        $updateData = [
            'trang_thai' => $validated['trang_thai'],
        ];

        if (isset($validated['tra_loi'])) {
            $updateData['tra_loi'] = $validated['tra_loi'];
            if ($validated['trang_thai'] === 'da_tra_loi') {
                $updateData['ngay_tra_loi'] = now();
                $updateData['nguoi_phu_trach_id'] = $request->user('sanctum')?->id;
            }
        }

        $lienHe->update($updateData);

        if ($validated['trang_thai'] === 'da_tra_loi' && !empty($updateData['tra_loi'])) {
            $user = NguoiDung::where('email', $lienHe->email)->first();
            if ($user) {
                ThongBao::create([
                    'nguoi_dung_id' => $user->id,
                    'tieu_de' => 'Phản hồi: ' . $lienHe->chu_de,
                    'noi_dung' => $updateData['tra_loi'],
                    'loai' => 'inbox', // ThongBao::LOAI_INBOX
                    'da_doc' => 0,
                ]);
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Cập nhật tin nhắn liên hệ thành công',
            'data' => $lienHe->fresh(),
        ]);
    }

    public function destroy(LienHe $lienHe): JsonResponse
    {
        $lienHe->delete();

        return response()->json([
            'status' => true,
            'message' => 'Đã xóa tin nhắn liên hệ',
        ]);
    }
}
