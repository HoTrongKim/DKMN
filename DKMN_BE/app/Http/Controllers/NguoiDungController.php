<?php

namespace App\Http\Controllers;

use App\Models\NguoiDung;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\ResetOtpMail;

/**
 * Controller xử lý authentication và quản lý profile người dùng
 * Đăng ký, đăng nhập, quên mật khẩu (OTP qua email), đổi mật khẩu
 * Cập nhật thông tin cá nhân, lấy thông tin user hiện tại
 */
class NguoiDungController extends Controller
{
    /**
     * Lấy danh sách tất cả người dùng (admin/internal)
     */
    public function getData()
    {
        // Trả về JSON response
        return response()->json(['data' => NguoiDung::orderByDesc('ngay_tao')->get()]);
    }

    public function dangKy(Request $request)
    {
        // Chuẩn hóa input: hỗ trợ nhiều tên field từ frontend (email/tai_khoan, mat_khau/password/matKhau)
        $emailInput = $request->input('email', $request->input('tai_khoan'));
        $passwordRaw = $request->input('mat_khau', $request->input('password', $request->input('matKhau')));

        // Merge dữ liệu đã chuẩn hóa vào request: trim space, lowercase email
        $request->merge([
            'ho_ten' => $request->filled('ho_ten') ? trim($request->input('ho_ten')) : null,
            'email' => $emailInput ? strtolower(trim($emailInput)) : null,
            'so_dien_thoai' => $request->filled('so_dien_thoai') ? trim($request->input('so_dien_thoai')) : null,
            'mat_khau' => is_string($passwordRaw) ? trim($passwordRaw) : null,
        ]);

        // Validate dữ liệu đăng ký
        // - ho_ten: bắt buộc, tối đa 100 ký tự
        // - email: bắt buộc, định dạng email, unique trong bảng nguoi_dungs
        // - so_dien_thoai: bắt buộc, phải đúng 10 chữ số
        // - mat_khau: bắt buộc, tối thiểu 4 ký tự
        $data = // Validate dữ liệu từ request
        $request->validate([
            'ho_ten' => 'required|string|max:100',
            'email' => 'required|string|email|max:100|unique:nguoi_dungs,email',
            'so_dien_thoai' => ['required', 'regex:/^\\d{10}$/'],
            'mat_khau' => 'required|string|min:4',
        ]);

        // Tạo người dùng mới
        $nguoiDung = NguoiDung::create([
            'ho_ten' => $data['ho_ten'],
            'email' => $data['email'],
            'so_dien_thoai' => $data['so_dien_thoai'] ?? null,
            'mat_khau' => Hash::make($data['mat_khau']), // Hash mật khẩu
            'vai_tro' => 'khach_hang',
            'trang_thai' => 'hoat_dong',
        ]);

        // Tạo token đăng nhập ngay sau khi đăng ký
        $token = $nguoiDung->createToken('key_client')->plainTextToken;

        // Trả về thông tin user và token để frontend lưu vào localStorage
        // Trả về JSON response
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
        // Chuẩn hóa input: hỗ trợ nhiều tên field từ frontend
        $emailInput = $request->input('email', $request->input('tai_khoan'));
        $passwordRaw = $request->input('password', $request->input('mat_khau', $request->input('matKhau')));

        // Merge dữ liệu đã chuẩn hóa: trim space, lowercase email
        $request->merge([
            'email' => $emailInput ? strtolower(trim($emailInput)) : null,
            'mat_khau' => is_string($passwordRaw) ? trim($passwordRaw) : $passwordRaw,
        ]);

        // Validate credentials: email bắt buộc, password hoặc mat_khau phải có một trong hai
        $credentials = // Validate dữ liệu từ request
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required_without_all:mat_khau,matKhau|nullable|string|min:4',
            'mat_khau' => 'required_without:password|nullable|string|min:4',
        ]);

        // Lấy password từ một trong các field có thể
        $passwordInput = $credentials['password']
            ?? $credentials['mat_khau']
            ?? ($passwordRaw && is_string($passwordRaw) ? trim($passwordRaw) : null);

        // Tìm user theo email
        // Thao tác database
        $nguoiDung = NguoiDung::where('email', $request->email)->first();

        // Kiểm tra user tồn tại, có password, và password khớp
        // passwordMatches() hỗ trợ cả bcrypt và plaintext (legacy migration)
        if (
            !$nguoiDung ||
            !$passwordInput ||
            !$this->passwordMatches($nguoiDung, $passwordInput)
        ) {
            // Trả về JSON response
        return response()->json([
                'status' => false,
                'message' => 'Tài khoản hoặc mật khẩu không chính xác',
            ]);
        }

        // Kiểm tra trạng thái tài khoản: chỉ cho phép 'hoat_dong'
        if ($nguoiDung->trang_thai !== 'hoat_dong') {
            // Trả về JSON response
        return response()->json([
                'status' => 0,
                'message' => 'Tai khoan chua duoc kich hoat',
            ]);
        }

        // Tạo token đăng nhập
        $token = $nguoiDung->createToken('key_client')->plainTextToken;
        
        // Redirect URL dựa vào vai trò: admin -> /ADMIN, khách hàng -> /
        $redirectUrl = $nguoiDung->vai_tro === 'quan_tri' ? '/ADMIN' : '/';

        // Trả về token và thông tin user để frontend lưu vào localStorage
        // Trả về JSON response
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
            // Trả về JSON response
        return response()->json([
                'status' => false,
                'message' => 'Yêu cầu đăng nhập.',
            ], 401);
        }

        // Trả về JSON response
        return response()->json([
            'status' => true,
            'data' => $this->serializeUser($user),
        ]);
    }

    public function capNhatThongTin(Request $request)
    {
        $user = $request->user('sanctum') ?? $request->user();
        if (!$user) {
            // Trả về JSON response
        return response()->json([
                'status' => false,
                'message' => 'Yêu cầu đăng nhập.',
            ], 401);
        }

        $data = // Validate dữ liệu từ request
        $request->validate([
            'ho_ten' => 'required|string|max:100',
            'so_dien_thoai' => 'nullable|string|max:20',
        ]);

        $user->forceFill([
            'ho_ten' => $data['ho_ten'],
            'so_dien_thoai' => $data['so_dien_thoai'] ?? null,
            'ngay_cap_nhat' => now(),
        ])->save();

        // Trả về JSON response
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
            // Trả về JSON response
        return response()->json([
                'status' => false,
                'message' => 'Yêu cầu đăng nhập.',
            ], 401);
        }

        $data = // Validate dữ liệu từ request
        $request->validate([
            'currentPassword' => 'required|string|min:4',
            'newPassword' => 'required|string|min:6|different:currentPassword',
            'confirmPassword' => 'required|string|same:newPassword',
        ], [
            'confirmPassword.same' => 'Xác nhận mật khẩu không khớp.',
        ]);

        if (!$this->passwordMatches($user, $data['currentPassword'])) {
            // Trả về JSON response
        return response()->json([
                'status' => false,
                'message' => 'Mật khẩu hiện tại không chính xác.',
            ], 422);
        }

        $user->forceFill([
            'mat_khau' => // Xử lý mã hóa/kiểm tra password
        Hash::make($data['newPassword']),
            'ngay_cap_nhat' => now(),
        ])->save();

        // Trả về JSON response
        return response()->json([
            'status' => true,
            'message' => 'Đã cập nhật mật khẩu.',
        ]);
    }

    public function quenMatKhau(Request $request)
    {
        // Validate email
        $data = // Validate dữ liệu từ request
        $request->validate([
            'email' => 'required|email|max:150',
        ]);
        
        // Tìm user theo email
        // Thao tác database
        $user = NguoiDung::where('email', $data['email'])->first();
        if (!$user) {
            // Trả về JSON response
        return response()->json([
                'status' => false,
                'message' => 'Email không tồn tại trên hệ thống.',
            ], 404);
        }

        // Tạo mã OTP ngẫu nhiên 6 chữ số (100000-999999)
        $otp = random_int(100000, 999999);
        
        // Hash OTP trước khi lưu vào database (bảo mật)
        $hashedOtp = // Xử lý mã hóa/kiểm tra password
        Hash::make((string) $otp);

        // Lưu OTP vào bảng password_reset_tokens (hash OTP để bảo mật)
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $data['email']],
            ['token' => $hashedOtp, 'created_at' => now()]
        );

        // Gửi email chứa mã OTP cho user
        try {
            // Gửi email chứa OTP
            Mail::to($data['email'])->send(new ResetOtpMail($otp));
        } catch (\Throwable $e) {
            // Không lộ lỗi mail ra client để tránh leak thông tin
            // Chỉ log lỗi vào hệ thống
            report($e);
        }

        // Trả về success ngay cả khi mail fail (UX tốt hơn)
        // Trả về JSON response
        return response()->json([
            'status' => true,
            'message' => 'Mã OTP đã được gửi.',
        ]);
    }

    public function xacThucOtp(Request $request)
    {
        // Validate email và OTP
        $validated = // Validate dữ liệu từ request
        $request->validate([
            'email' => 'required|email|max:150',
            'otp' => 'required|string|max:10',
        ]);

        // Lấy OTP record từ bảng password_reset_tokens
        $record = DB::table('password_reset_tokens')
            ->where('email', $validated['email'])
            ->first();

        // Kiểm tra OTP tồn tại
        if (!$record) {
            // Trả về JSON response
        return response()->json([
                'status' => false,
                'message' => 'Mã OTP không hợp lệ hoặc đã hết hạn.',
            ], 422);
        }

        // Parse thời gian tạo OTP, fallback về 2 giờ trước nếu null
        $created = $record->created_at ? Carbon::parse($record->created_at) : now()->subHours(2);
        
        // Kiểm tra OTP còn hiệu lực (10 phút)
        if ($created->lt(now()->subMinutes(10))) {
            // Xóa OTP đã hết hạn
            DB::table('password_reset_tokens')->where('email', $validated['email'])->delete();
            // Trả về JSON response
        return response()->json([
                'status' => false,
                'message' => 'Mã OTP đã hết hạn. Vui lòng yêu cầu lại.',
            ], 422);
        }

        // Kiểm tra OTP nhập vào có khớp với OTP đã hash trong DB không
        if (!// Xử lý mã hóa/kiểm tra password
        Hash::check($validated['otp'], $record->token)) {
            // Trả về JSON response
        return response()->json([
                'status' => false,
                'message' => 'Mã OTP không chính xác.',
            ], 422);
        }

        // OTP hợp lệ, cho phép user đặt lại mật khẩu
        // Trả về JSON response
        return response()->json([
            'status' => true,
            'message' => 'OTP hợp lệ, bạn có thể đặt lại mật khẩu.',
        ]);
    }

    public function datLaiMatKhau(Request $request)
    {
        $validated = // Validate dữ liệu từ request
        $request->validate([
            'email' => 'required|email|max:150',
            'otp' => 'required|string|max:10',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'password.confirmed' => 'Mật khẩu xác nhận không khớp.',
        ]);

        $record = DB::table('password_reset_tokens')
            ->where('email', $validated['email'])
            ->first();

        if (!$record) {
            // Trả về JSON response
        return response()->json([
                'status' => false,
                'message' => 'Mã OTP không hợp lệ hoặc đã hết hạn.',
            ], 422);
        }

        $created = $record->created_at ? Carbon::parse($record->created_at) : now()->subHours(2);
        if ($created->lt(now()->subMinutes(60))) {
            DB::table('password_reset_tokens')->where('email', $validated['email'])->delete();
            // Trả về JSON response
        return response()->json([
                'status' => false,
                'message' => 'Mã OTP đã hết hạn. Vui lòng yêu cầu lại.',
            ], 422);
        }

        if (!// Xử lý mã hóa/kiểm tra password
        Hash::check($validated['otp'], $record->token)) {
            // Trả về JSON response
        return response()->json([
                'status' => false,
                'message' => 'Mã OTP không chính xác.',
            ], 422);
        }

        // Thao tác database
        $user = NguoiDung::where('email', $validated['email'])->first();
        if (!$user) {
            DB::table('password_reset_tokens')->where('email', $validated['email'])->delete();
            // Trả về JSON response
        return response()->json([
                'status' => false,
                'message' => 'Người dùng không tồn tại.',
            ], 404);
        }

        // Cập nhật mật khẩu mới và xóa token
        $user->forceFill([
            'mat_khau' => // Xử lý mã hóa/kiểm tra password
        Hash::make($validated['password']),
            'ngay_cap_nhat' => now(),
        ])->save();

        DB::table('password_reset_tokens')->where('email', $validated['email'])->delete();

        // Trả về JSON response
        return response()->json([
            'status' => true,
            'message' => 'Đặt lại mật khẩu thành công. Vui lòng đăng nhập lại.',
        ]);
    }

    private function passwordMatches(NguoiDung $nguoiDung, string $passwordInput): bool
    {
        // Lấy mật khẩu đã lưu trong database
        $storedPassword = (string) $nguoiDung->mat_khau;

        // Kiểm tra mật khẩu không rỗng
        if ($storedPassword === '') {
            return false;
        }

        // TH1: Mật khẩu đã hash bằng bcrypt
        // Kiểm tra format bcrypt ($2y$, $2a$, $2b$ + 60 ký tự)
        if ($this->isBcryptHash($storedPassword)) {
            try {
                // So sánh password input với hash
                if (// Xử lý mã hóa/kiểm tra password
        Hash::check($passwordInput, $storedPassword)) {
                    return true;
                }
            } catch (\RuntimeException $e) {
                // Nếu Hash::check fail, fallback xuống kiểm tra plaintext
            }
        }

        // TH2: Mật khẩu lưu dạng plaintext (legacy data migration)
        // So sánh constant-time để tránh timing attack
        if (hash_equals($passwordInput, $storedPassword)) {
            // Tự động rehash password thành bcrypt khi user login với plaintext
            $nguoiDung->forceFill([
                'mat_khau' => // Xử lý mã hóa/kiểm tra password
        Hash::make($passwordInput),
                'ngay_cap_nhat' => now(),
            ])->save();

            return true;
        }

        // Mật khẩu không khớp
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

