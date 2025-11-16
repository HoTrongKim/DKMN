<?php

namespace App\Http\Controllers;

use App\Models\NguoiDung;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class NguoiDungController extends Controller
{
    public function getData()
    {
        return response()->json(['data' => NguoiDung::orderByDesc('ngay_tao')->get()]);
    }

    public function dangKy(Request $request)
    {
        $emailInput = $request->input('email', $request->input('tai_khoan'));
        $passwordRaw = $request->input('mat_khau', $request->input('password', $request->input('matKhau')));

        $request->merge([
            'ho_ten' => $request->filled('ho_ten') ? trim($request->input('ho_ten')) : null,
            'email' => $emailInput ? strtolower(trim($emailInput)) : null,
            'so_dien_thoai' => $request->filled('so_dien_thoai') ? trim($request->input('so_dien_thoai')) : null,
            'mat_khau' => is_string($passwordRaw) ? trim($passwordRaw) : null,
        ]);

        $data = $request->validate([
            'ho_ten' => 'required|string|max:100',
            'email' => 'required|string|email|max:100|unique:nguoi_dungs,email',
            'so_dien_thoai' => 'nullable|string|max:20',
            'mat_khau' => 'required|string|min:4',
        ]);

        $nguoiDung = NguoiDung::create([
            'ho_ten' => $data['ho_ten'],
            'email' => $data['email'],
            'so_dien_thoai' => $data['so_dien_thoai'] ?? null,
            'mat_khau' => Hash::make($data['mat_khau']),
            'vai_tro' => 'khach_hang',
            'trang_thai' => 'hoat_dong',
        ]);

        $token = $nguoiDung->createToken('key_client')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'Dang ky thanh cong',
            'token' => $token,
            'redirect_url' => '/',
            'data' => [
                'id' => $nguoiDung->id,
                'ho_ten' => $nguoiDung->ho_ten,
                'email' => $nguoiDung->email,
                'so_dien_thoai' => $nguoiDung->so_dien_thoai,
            ],
        ], 201);
    }

    public function dangNhap(Request $request)
    {
        $emailInput = $request->input('email', $request->input('tai_khoan'));
        $passwordRaw = $request->input('password', $request->input('mat_khau', $request->input('matKhau')));

        $request->merge([
            'email' => $emailInput ? strtolower(trim($emailInput)) : null,
            'mat_khau' => is_string($passwordRaw) ? trim($passwordRaw) : $passwordRaw,
        ]);

        $credentials = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required_without_all:mat_khau,matKhau|nullable|string|min:4',
            'mat_khau' => 'required_without:password|nullable|string|min:4',
        ]);

        $passwordInput = $credentials['password']
            ?? $credentials['mat_khau']
            ?? ($passwordRaw && is_string($passwordRaw) ? trim($passwordRaw) : null);

        $nguoiDung = NguoiDung::where('email', $request->email)->first();

        if (
            !$nguoiDung ||
            !$passwordInput ||
            !$this->passwordMatches($nguoiDung, $passwordInput)
        ) {
            return response()->json([
                'status' => false,
                'message' => 'Tài khoản hoặc mật khẩu không chính xác',
            ]);
        }

        if ($nguoiDung->trang_thai !== 'hoat_dong') {
            return response()->json([
                'status' => 0,
                'message' => 'Tai khoan chua duoc kich hoat',
            ]);
        }

        $token = $nguoiDung->createToken('key_client')->plainTextToken;
        $redirectUrl = $nguoiDung->vai_tro === 'quan_tri' ? '/ADMIN' : '/';

        return response()->json([
            'status' => true,
            'message' => 'Dang nhap thanh cong',
            'token' => $token,
            'redirect_url' => $redirectUrl,
            'redirectUrl' => $redirectUrl,
            'data' => $this->serializeUser($nguoiDung),
        ]);
    }

    public function thongTin(Request $request)
    {
        $user = $request->user('sanctum') ?? $request->user();
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Yêu cầu đăng nhập.',
            ], 401);
        }

        return response()->json([
            'status' => true,
            'data' => $this->serializeUser($user),
        ]);
    }

    public function capNhatThongTin(Request $request)
    {
        $user = $request->user('sanctum') ?? $request->user();
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Yêu cầu đăng nhập.',
            ], 401);
        }

        $data = $request->validate([
            'ho_ten' => 'required|string|max:100',
            'so_dien_thoai' => 'nullable|string|max:20',
        ]);

        $user->forceFill([
            'ho_ten' => $data['ho_ten'],
            'so_dien_thoai' => $data['so_dien_thoai'] ?? null,
            'ngay_cap_nhat' => now(),
        ])->save();

        return response()->json([
            'status' => true,
            'message' => 'Đã cập nhật thông tin tài khoản.',
            'data' => $this->serializeUser($user),
        ]);
    }

    public function doiMatKhau(Request $request)
    {
        $user = $request->user('sanctum') ?? $request->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Yêu cầu đăng nhập.',
            ], 401);
        }

        $data = $request->validate([
            'currentPassword' => 'required|string|min:4',
            'newPassword' => 'required|string|min:6|different:currentPassword',
            'confirmPassword' => 'required|string|same:newPassword',
        ], [
            'confirmPassword.same' => 'Xác nhận mật khẩu không khớp.',
        ]);

        if (!$this->passwordMatches($user, $data['currentPassword'])) {
            return response()->json([
                'status' => false,
                'message' => 'Mật khẩu hiện tại không chính xác.',
            ], 422);
        }

        $user->forceFill([
            'mat_khau' => Hash::make($data['newPassword']),
            'ngay_cap_nhat' => now(),
        ])->save();

        return response()->json([
            'status' => true,
            'message' => 'Đã cập nhật mật khẩu.',
        ]);
    }

    private function passwordMatches(NguoiDung $nguoiDung, string $passwordInput): bool
    {
        $storedPassword = (string) $nguoiDung->mat_khau;

        if ($storedPassword === '') {
            return false;
        }

        if ($this->isBcryptHash($storedPassword)) {
            try {
                if (Hash::check($passwordInput, $storedPassword)) {
                    return true;
                }
            } catch (\RuntimeException $e) {
                // fall back to manual comparison + rehash below
            }
        }

        if (hash_equals($passwordInput, $storedPassword)) {
            $nguoiDung->forceFill([
                'mat_khau' => Hash::make($passwordInput),
                'ngay_cap_nhat' => now(),
            ])->save();

            return true;
        }

        return false;
    }

    private function isBcryptHash(string $hash): bool
    {
        return strlen($hash) === 60 && Str::startsWith($hash, ['$2y$', '$2a$', '$2b$']);
    }

    private function serializeUser(NguoiDung $user): array
    {
        return [
            'id' => $user->id,
            'ho_ten' => $user->ho_ten,
            'email' => $user->email,
            'so_dien_thoai' => $user->so_dien_thoai,
            'vai_tro' => $user->vai_tro,
        ];
    }
}
