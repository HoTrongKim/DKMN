<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DanhGia;
use App\Models\DonHang;
use App\Models\HuyVe;
use App\Models\NguoiDung;
use App\Models\NhatKyHoatDong;
use App\Models\PhanHoi;
use App\Models\ThongBao;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Controller quản lý người dùng (admin + khách hàng)
 * CRUD user, phân quyền, khóa/mở tài khoản
 */
class UserAdminController extends Controller
{
    /**
     * Danh sách người dùng có filter: keyword, status (active/locked), role (admin/customer)
     * Trả về paginated data
     * Logic:
     * - Tìm kiếm theo tên, email hoặc số điện thoại
     * - Lọc theo trạng thái hoạt động và vai trò người dùng
     * - Sắp xếp theo ngày tạo mới nhất
     */
    /**
     * Danh sách người dùng có filter: keyword, status (active/locked), role (admin/customer)
     * Trả về paginated data
     * Logic:
     * - Tìm kiếm theo tên, email hoặc số điện thoại
     * - Lọc theo trạng thái hoạt động và vai trò người dùng
     * - Sắp xếp theo ngày tạo mới nhất
     */
        /**
     * Lấy danh sách dữ liệu với phân trang và filter
     */
    public function index(Request $request)
    {
        // Validate các tham số lọc đầu vào
        $validated = $request->validate([
            'keyword' => 'nullable|string|max:150', // Từ khóa tìm kiếm
            'status' => 'nullable|string|in:active,locked', // Trạng thái tài khoản
            'role' => 'nullable|string|in:customer,admin', // Vai trò người dùng
        ]);

        $query = NguoiDung::query()->orderByDesc('ngay_tao'); // Mới nhất lên đầu

        // Xử lý tìm kiếm từ khóa
        if (!empty($validated['keyword'])) {
            $keyword = Str::lower(trim($validated['keyword']));
            // Tìm kiếm trong tên, email hoặc số điện thoại (nhóm điều kiện OR)
            $query->where(function ($sub) use ($keyword) {
                $sub->where('ho_ten', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%")
                    ->orWhere('so_dien_thoai', 'like', "%{$keyword}%");
            });
        }

        // Xử lý lọc theo trạng thái
        if (!empty($validated['status'])) {
            // Map từ frontend status (active/locked) sang DB status (hoat_dong/khoa)
            $query->where('trang_thai', $validated['status'] === 'active' ? 'hoat_dong' : 'khoa');
        }

        // Xử lý lọc theo vai trò
        if (!empty($validated['role'])) {
            // Map từ frontend role (admin/customer) sang DB role (quan_tri/khach_hang)
            $query->where('vai_tro', $validated['role'] === 'admin' ? 'quan_tri' : 'khach_hang');
        }

        // Phân trang và transform dữ liệu
        $paginator = $query->paginate($this->resolvePerPage($request));
        $data = $paginator->getCollection()->map(fn (NguoiDung $user) => $this->transformUser($user));

        return $this->respondWithPagination($paginator, $data);
    }

    /**
     * Tạo người dùng mới (admin hoặc customer)
     * Require: name, email, phone, password, role, status
     */
        /**
     * Tạo mới bản ghi
     */
    public function store(Request $request)
    {
        // Validate thông tin tạo mới
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|string|email|max:100|unique:nguoi_dungs,email', // Email phải duy nhất
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:6', // Mật khẩu tối thiểu 6 ký tự
            'role' => ['required', Rule::in(['customer', 'admin'])],
            'status' => ['required', Rule::in(['active', 'locked'])],
        ]);

        // Tạo user mới với mật khẩu đã hash
        $user = NguoiDung::create([
            'ho_ten' => $validated['name'],
            'email' => Str::lower($validated['email']),
            'so_dien_thoai' => $validated['phone'] ?? null,
            'mat_khau' => // Xử lý mã hóa/kiểm tra password
        Hash::make($validated['password']),
            'vai_tro' => $validated['role'] === 'admin' ? 'quan_tri' : 'khach_hang',
            'trang_thai' => $validated['status'] === 'active' ? 'hoat_dong' : 'khoa',
        ]);

        // Trả về JSON response
        return response()->json([
            'status' => true,
            'data' => $this->transformUser($user),
        ], 201);
    }

    /**
     * Cập nhật thông tin người dùng
     * Cho phép sửa: name, email, phone, role, status, password
     */
        /**
     * Cập nhật bản ghi theo ID
     */
    public function update(Request $request, NguoiDung $nguoiDung)
    {
        // Validate dữ liệu cập nhật (cho phép partial update)
        $validated = $request->validate([
            'name' => 'nullable|string|max:100',
            'email' => [
                'nullable',
                'string',
                'email',
                'max:100',
                Rule::unique('nguoi_dungs', 'email')->ignore($nguoiDung->id), // Email duy nhất trừ chính user này
            ],
            'phone' => 'nullable|string|max:20',
            'role' => 'nullable|string|in:customer,admin',
            'status' => 'nullable|string|in:active,locked',
            'password' => 'nullable|string|min:6',
        ]);

        $payload = [];

        // Chỉ cập nhật các trường có trong request
        if (!empty($validated['name'])) {
            $payload['ho_ten'] = $validated['name'];
        }

        if (!empty($validated['email'])) {
            $payload['email'] = Str::lower($validated['email']);
        }

        if (!empty($validated['phone'])) {
            $payload['so_dien_thoai'] = $validated['phone'];
        }

        if (!empty($validated['role'])) {
            $payload['vai_tro'] = $validated['role'] === 'admin' ? 'quan_tri' : 'khach_hang';
        }

        if (!empty($validated['status'])) {
            $payload['trang_thai'] = $validated['status'] === 'active' ? 'hoat_dong' : 'khoa';
        }

        // Nếu có đổi mật khẩu thì hash lại
        if (!empty($validated['password'])) {
            $payload['mat_khau'] = // Xử lý mã hóa/kiểm tra password
        Hash::make($validated['password']);
        }

        if (!empty($payload)) {
            $payload['ngay_cap_nhat'] = now();
            $nguoiDung->fill($payload)->save();
        }

        // Trả về JSON response
        return response()->json([
            'status' => true,
            'data' => $this->transformUser($nguoiDung->fresh()),
        ]);
    }

    /**
     * Cập nhật trạng thái người dùng (active/locked)
     * Nhanh hơn update vì chỉ sửa status
     */
    public function updateStatus(Request $request, NguoiDung $nguoiDung)
    {
        $validated = // Validate dữ liệu từ request
        $request->validate([
            'status' => ['required', Rule::in(['active', 'locked'])],
        ]);

        $nguoiDung->fill([
            'trang_thai' => $validated['status'] === 'active' ? 'hoat_dong' : 'khoa',
            'ngay_cap_nhat' => now(),
        ])->save();

        // Trả về JSON response
        return response()->json([
            'status' => true,
            'data' => $this->transformUser($nguoiDung->fresh()),
        ]);
    }

    /**
     * Xóa người dùng khỏi hệ thống
     * Kiểm tra không cho xóa chính mình, nullify foreign keys trước khi xóa
     * Sử dụng DB transaction để đảm bảo tính toàn vẹn dữ liệu
     */
        /**
     * Xóa bản ghi theo ID
     */
    public function destroy(Request $request, NguoiDung $nguoiDung)
    {
        // Không cho phép tự xóa tài khoản đang đăng nhập
        $authId = $request->user()?->id;
        if ($authId && $nguoiDung->id === (int) $authId) {
            // Trả về JSON response
        return response()->json([
                'status' => false,
                'message' => 'Khong the xoa tai khoan dang dang nhap.',
            ], 422);
        }

        try {
            // Sử dụng transaction để đảm bảo xóa sạch hoặc rollback nếu lỗi
            DB::transaction(function () use ($nguoiDung) {
                $userId = $nguoiDung->id;

                // Set null cho các quan hệ thay vì xóa (để giữ lịch sử)
                DonHang::where('nguoi_dung_id', $userId)->update(['nguoi_dung_id' => null]);
                ThongBao::where('nguoi_dung_id', $userId)->update(['nguoi_dung_id' => null]);
                NhatKyHoatDong::where('nguoi_dung_id', $userId)->update(['nguoi_dung_id' => null]);
                PhanHoi::where('nguoi_dung_id', $userId)->update(['nguoi_dung_id' => null]);
                PhanHoi::where('nguoi_phu_trach', $userId)->update(['nguoi_phu_trach' => null]);
                HuyVe::where('nguoi_xu_ly', $userId)->update(['nguoi_xu_ly' => null]);
                
                // Xóa đánh giá của user
                DanhGia::where('nguoi_dung_id', $userId)->delete();

                // Cuối cùng xóa user
                $nguoiDung->delete();
            });
        } catch (\Throwable $exception) {
            report($exception);
            // Trả về JSON response
        return response()->json([
                'status' => false,
                'message' => 'Khong the xoa nguoi dung do dang duoc su dung o khu vuc khac.',
            ], 422);
        }

        // Trả về JSON response
        return response()->json([
            'status' => true,
            'message' => 'Da xoa nguoi dung.',
        ]);
    }

    /**
     * Transform user object sang format API response
     * Map role và status labels tiếng Việt
     */
    private function transformUser(NguoiDung $user): array
    {
        $roleCode = $this->normalizeRole($user->vai_tro);
        $statusCode = $this->normalizeStatus($user->trang_thai);

        return [
            'id' => $user->id,
            'name' => $user->ho_ten,
            'email' => $user->email,
            'phone' => $user->so_dien_thoai,
            'role' => $this->mapRoleLabel($roleCode),
            'roleCode' => $roleCode,
            'status' => $this->mapStatusLabel($statusCode),
            'statusCode' => $statusCode,
            'createdAt' => optional($user->ngay_tao)->toIso8601String(),
            'createdAtText' => $this->formatDisplayDate($user->ngay_tao),
        ];
    }

    /**
     * Normalize role: quan_tri → admin, khác → customer
     */
    private function normalizeRole(?string $role): string
    {
        return $role === 'quan_tri' ? 'admin' : 'customer';
    }

    /**
     * Normalize status: khoa → locked, khác → active
     */
    private function normalizeStatus(?string $status): string
    {
        return $status === 'khoa' ? 'locked' : 'active';
    }

    /**
     * Map role code sang label tiếng Việt
     */
    private function mapRoleLabel(string $roleCode): string
    {
        return match ($roleCode) {
            'admin' => 'Quan tri',
            default => 'Khach hang',
        };
    }

    /**
     * Map status code sang label tiếng Việt
     */
    private function mapStatusLabel(string $statusCode): string
    {
        return match ($statusCode) {
            'locked' => 'Da khoa',
            default => 'Hoat dong',
        };
    }

    /**
     * Format datetime cho display (mặc định: d/m/Y H:i)
     */
    private function formatDisplayDate($value, string $format = 'd/m/Y H:i'): ?string
    {
        if (!$value) {
            return null;
        }

        try {
            return Carbon::parse($value)->format($format);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
