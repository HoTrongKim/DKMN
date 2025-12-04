<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DonHang;
use App\Models\DanhGia;
use App\Models\Ghe;
use App\Models\Payment;
use App\Models\ThanhToan;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Controller quản lý đơn hàng/vé cho Admin
 * CRUD orders, cập nhật status, giải phóng ghế khi hủy đơn
 * Xử lý logic refund và nullify foreign keys
 */
class OrderAdminController extends Controller
{
    /**
     * Danh sách đơn hàng có filter: search (mã đơn/tên khách/email/phone), status, paymentStatus
     * Eager load: chuyến đi, trạm, nhà vận hành, user, tickets, thanh toán, ghế
     * Logic:
     * - Xây dựng query với các điều kiện lọc động
     * - Tìm kiếm theo keyword trên nhiều trường (mã đơn, tên khách, sdt, địa điểm)
     * - Lọc theo trạng thái đơn hàng và trạng thái thanh toán (dựa trên quan hệ thanhToans)
     * - Phân trang và transform dữ liệu trả về
     */
    /**
     * Danh sách đơn hàng có filter: search (mã đơn/tên khách/email/phone), status, paymentStatus
     * Eager load: chuyến đi, trạm, nhà vận hành, user, tickets, thanh toán, ghế
     * Logic:
     * - Xây dựng query với các điều kiện lọc động
     * - Tìm kiếm theo keyword trên nhiều trường (mã đơn, tên khách, sdt, địa điểm)
     * - Lọc theo trạng thái đơn hàng và trạng thái thanh toán (dựa trên quan hệ thanhToans)
     * - Phân trang và transform dữ liệu trả về
     */
        /**
     * Lấy danh sách dữ liệu với phân trang và filter
     */
    public function index(Request $request)
    {
        // Validate các tham số filter từ request
        $validated = $request->validate([
            'search' => 'nullable|string|max:150',
            'status' => ['nullable', Rule::in(['da_dat', 'dang_xu_ly', 'da_di', 'da_huy'])],
            'paymentStatus' => ['nullable', Rule::in(['paid', 'pending', 'refunded'])],
        ]);

        // Khởi tạo query builder với eager loading các quan hệ cần thiết
        // Eager load sâu (nested) để lấy thông tin trạm đi/đến và nhà vận hành của chuyến đi
        $query = DonHang::query()
            ->with([
                'chuyenDi.tramDi',
                'chuyenDi.tramDen',
                'chuyenDi.nhaVanHanh',
                'nguoiDung',
                'ticket',
                'thanhToans',
                'chiTietDonHang.ghe', // Load ghế để hiển thị số ghế/tên ghế
            ])
            ->orderByDesc('ngay_tao'); // Mặc định sắp xếp đơn mới nhất lên đầu

        // Xử lý logic tìm kiếm (Search)
        if (!empty($validated['search'])) {
            $rawSearch = trim($validated['search']);
            $keyword = Str::lower($rawSearch);
            $idSearch = null;
            // Nếu search string là số, thử tìm theo ID đơn hàng
            if (preg_match('/^\s*#?(\d+)\s*$/', $rawSearch, $matches)) {
                $idSearch = (int) $matches[1];
            }

            // Sử dụng where closure để gom nhóm các điều kiện OR
            $query->where(function ($sub) use ($keyword, $idSearch) {
                // Tìm kiếm tương đối (LIKE) trên các trường thông tin chính
                $sub->whereRaw('LOWER(ma_don) LIKE ?', ["%{$keyword}%"])
                    ->orWhereRaw('LOWER(ten_khach) LIKE ?', ["%{$keyword}%"])
                    ->orWhereRaw('LOWER(sdt_khach) LIKE ?', ["%{$keyword}%"])
                    ->orWhereRaw('LOWER(noi_di) LIKE ?', ["%{$keyword}%"])
                    ->orWhereRaw('LOWER(noi_den) LIKE ?', ["%{$keyword}%"]);

                // Nếu keyword là số, thêm điều kiện tìm theo ID
                if (!is_null($idSearch)) {
                    $sub->orWhere('id', $idSearch);
                }
            });
        }

        // Xử lý logic lọc theo trạng thái đơn hàng (Status)
        if (!empty($validated['status'])) {
            // Map từ status FE (da_dat...) sang status DB (da_xac_nhan...)
            $query->where('trang_thai', $this->mapFilterStatus($validated['status']));
        }

        // Xử lý logic lọc theo trạng thái thanh toán (Payment Status)
        // Trạng thái thanh toán được xác định dựa trên bảng liên kết `thanh_toans`
        if (!empty($validated['paymentStatus'])) {
            $status = $validated['paymentStatus'];
            if ($status === 'paid') {
                // Đã thanh toán: có ít nhất 1 record thanh_toans với trạng thái 'thanh_cong'
                $query->whereHas('thanhToans', fn ($q) => $q->where('trang_thai', 'thanh_cong'));
            } elseif ($status === 'refunded') {
                // Đã hoàn tiền: có record thanh_toans với trạng thái 'hoan_tien'
                $query->whereHas('thanhToans', fn ($q) => $q->where('trang_thai', 'hoan_tien'));
            } else {
                // Chưa thanh toán (pending): KHÔNG có record thanh_toans nào thành công
                $query->whereDoesntHave('thanhToans', fn ($q) => $q->where('trang_thai', 'thanh_cong'));
            }
        }

        // Phân trang kết quả và transform dữ liệu trước khi trả về
        $paginator = $query->paginate($this->resolvePerPage($request));
        $data = $paginator->getCollection()
            ->map(fn (DonHang $order) => $this->transformOrder($order));

        return $this->respondWithPagination($paginator, $data);
    }

    /**
     * Chi tiết đơn hàng: order info + items (tickets/seats) + payments
     * Eager load: chuyến đi chi tiết, ghế, thanh toán
     */
        /**
     * Lấy chi tiết một bản ghi theo ID
     */
    public function show(DonHang $donHang)
    {
        // Load thêm các quan hệ chi tiết phục vụ cho trang detail
        $donHang->load([
            'chuyenDi.tramDi.tinhThanh', // Load tỉnh thành để hiển thị địa chỉ đầy đủ
            'chuyenDi.tramDen.tinhThanh',
            'chuyenDi.nhaVanHanh',
            'chiTietDonHang.ghe',
            'thanhToans',
            'ticket',
        ]);

        // Transform thông tin cơ bản
        $order = $this->transformOrder($donHang);
        
        // Map danh sách ghế/vé trong đơn hàng
        $order['items'] = $donHang->chiTietDonHang->map(function ($item) {
            return [
                'seatId' => $item->ghe_id,
                'seatLabel' => $item->ghe?->so_ghe,
                'passenger' => $item->ten_hanh_khach,
                'phone' => $item->sdt_hanh_khach,
                'price' => (float) $item->gia_ghe,
            ];
        });

        // Map lịch sử thanh toán của đơn hàng
        $order['payments'] = $donHang->thanhToans->map(function (ThanhToan $payment) {
            return [
                'id' => $payment->id,
                'gateway' => $payment->cong_thanh_toan,
                'amount' => (float) $payment->so_tien,
                'status' => $payment->trang_thai,
                'paidAt' => optional($payment->thoi_diem_thanh_toan)->toIso8601String(),
            ];
        });

        // Trả về JSON response
        return response()->json([
            'status' => true,
            'data' => $order,
        ]);
    }

    /**
     * Cập nhật đơn hàng: status (cho_xu_ly/da_xac_nhan/hoan_tat/da_huy), paymentStatus
     * Xử lý logic refund nếu cần
     */
        /**
     * Cập nhật bản ghi theo ID
     */
    public function update(Request $request, DonHang $donHang)
    {
        // Validate dữ liệu cập nhật
        $validated = $request->validate([
            'status' => ['nullable', Rule::in(['cho_xu_ly', 'da_xac_nhan', 'hoan_tat', 'da_huy'])],
            'paymentStatus' => ['nullable', Rule::in(['paid', 'pending', 'refunded'])],
            'paymentGateway' => 'nullable|string|max:50',
        ]);

        $changes = [];
        // Nếu có thay đổi trạng thái đơn hàng
        if (!empty($validated['status'])) {
            $changes['trang_thai'] = $validated['status'];
        }

        // Thực hiện update vào DB nếu có thay đổi
        if (!empty($changes)) {
            $changes['ngay_cap_nhat'] = now();
            $donHang->fill($changes)->save();
        }

        // Nếu có thay đổi trạng thái thanh toán (ví dụ admin xác nhận đã nhận tiền mặt)
        if (!empty($validated['paymentStatus'])) {
            $this->syncPaymentStatus(
                $donHang,
                $validated['paymentStatus'],
                $validated['paymentGateway'] ?? 'admin_manual' // Mặc định là admin xác nhận thủ công
            );
        }

        // Trả về JSON response
        return response()->json([
            'status' => true,
            'message' => 'Cập nhật đơn hàng thành công.',
            'data' => $donHang->fresh([
                'chuyenDi.tramDi',
                'chuyenDi.tramDen',
                'thanhToans',
            ]),
        ]);
    }

    /**
     * Xóa đơn hàng: giải phóng ghế, xóa ratings/payments, nullify foreign keys
     * Dùng DB transaction để đảm bảo tính toàn vẹn
     */
        /**
     * Xóa bản ghi theo ID
     */
    public function destroy(DonHang $donHang)
    {
        // Sử dụng transaction để đảm bảo atomicity (nguyên tử)
        // Nếu có lỗi xảy ra ở bất kỳ bước nào, toàn bộ thao tác sẽ được rollback
        DB::transaction(function () use ($donHang) {
            // 1. Xóa đánh giá liên quan đến đơn hàng
            DanhGia::where('don_hang_id', $donHang->id)->delete();
            
            // 2. Giải phóng ghế (set trạng thái ghế về 'trong' và cập nhật số ghế còn của chuyến)
            $this->releaseSeats($donHang);
            
            // 3. Xóa chi tiết đơn hàng (vé/ghế đã đặt)
            $donHang->chiTietDonHang()->delete();
            
            // 4. Xóa lịch sử thanh toán
            $donHang->thanhToans()->delete();
            
            // 5. Xóa vé điện tử (QR code) và payment online liên quan (nếu có)
            if ($ticket = $donHang->ticket()->first()) {
                Payment::where('ticket_id', $ticket->id)->delete();
                $ticket->delete();
            }
            
            // 6. Cuối cùng xóa đơn hàng
            $donHang->delete();
        });

        // Trả về JSON response
        return response()->json([
            'status' => true,
            'message' => 'Đã xóa đơn hàng.',
        ]);
    }

    /**
     * Transform order object sang format API response
     * Lấy thông tin: customer, trip, status, payment, tính toán labels
     */
    private function transformOrder(DonHang $order): array
    {
        $trip = $order->chuyenDi;
        // Fallback thông tin địa điểm nếu chuyến đi bị xóa
        $from = $trip?->tramDi?->ten ?? $order->noi_di;
        $to = $trip?->tramDen?->ten ?? $order->noi_den;
        $departureRaw = $trip?->gio_khoi_hanh;
        
        // Tính toán trạng thái thanh toán tổng hợp
        $paymentStatusCode = $this->resolvePaymentStatusCode($order);
        // Map trạng thái đơn hàng sang tiếng Việt
        $statusLabel = $this->mapStatusLabel($order->trang_thai);
        $user = $order->nguoiDung;

        // Thông tin tài khoản đặt vé
        $accountName = $user?->ho_ten;
        $accountEmail = $user?->email;
        $accountPhone = $user?->so_dien_thoai;

        // Thông tin hành khách (ưu tiên thông tin trong đơn, fallback về tài khoản)
        $customerName = $order->ten_khach ?: ($accountName ?: 'Khách lẻ');
        $customerPhone = $order->sdt_khach ?: $accountPhone;
        $customerEmail = $order->email_khach ?: $accountEmail;

        return [
            'id' => $order->id,
            'code' => $order->ma_don,
            'customerName' => $customerName,
            'customerPhone' => $customerPhone,
            'customerEmail' => $customerEmail,
            'accountName' => $accountName,
            'accountEmail' => $accountEmail,
            'accountPhone' => $accountPhone,
            'tripDetail' => trim(($from ?? '') . ' → ' . ($to ?? '')),
            'departureTime' => $this->formatDisplayDate($departureRaw),
            'departureTimeRaw' => $departureRaw ? Carbon::parse($departureRaw)->toIso8601String() : null,
            'orderDate' => $this->formatDisplayDate($order->ngay_tao),
            'orderDateRaw' => optional($order->ngay_tao)->toIso8601String(),
            'total' => (float) $order->tong_tien,
            'totalAmount' => (float) $order->tong_tien,
            'status' => $statusLabel,
            'rawStatus' => $order->trang_thai,
            'paymentStatus' => $this->mapPaymentLabel($paymentStatusCode),
            'paymentStatusCode' => $paymentStatusCode,
            'createdAt' => optional($order->ngay_tao)->toIso8601String(),
            'pickupStation' => $order->tram_don ?? $trip?->tramDi?->ten,
            'dropoffStation' => $order->tram_tra ?? $trip?->tramDen?->ten,
            'operator' => $trip?->nhaVanHanh?->ten,
        ];
    }

    /**
     * Map filter status từ frontend sang DB values
     */
    private function mapFilterStatus(string $status): string
    {
        return match ($status) {
            'da_dat' => 'da_xac_nhan',
            'dang_xu_ly' => 'cho_xu_ly',
            'da_di' => 'hoan_tat',
            'da_huy' => 'da_huy',
            default => 'cho_xu_ly',
        };
    }

    /**
     * Map order status sang label tiếng Việt
     */
    private function mapStatusLabel(?string $status): string
    {
        return match ($status) {
            'cho_xu_ly' => 'Đang xử lý',
            'da_xac_nhan' => 'Đã đặt',
            'hoan_tat' => 'Đã đi',
            'da_huy' => 'Đã hủy',
            default => 'Không xác định',
        };
    }

    /**
     * Resolve payment status code từ order's thanhToans
     */
    private function resolvePaymentStatusCode(DonHang $order): string
    {
        // Lấy giao dịch thanh toán mới nhất
        $latest = $order->thanhToans->first();
        return match ($latest?->trang_thai) {
            'thanh_cong' => 'paid',
            'hoan_tien' => 'refunded',
            default => 'pending',
        };
    }

    /**
     * Map payment status code sang label tiếng Việt (Đã TT/Hoàn tiền/Chờ TT)
     */
    private function mapPaymentLabel(string $statusCode): string
    {
        return match ($statusCode) {
            'paid' => 'Đã TT',
            'refunded' => 'Hoàn tiền',
            default => 'Chờ TT',
        };
    }

    /**
     * Đồng bộ payment status: tạo ThanhToan record mới với status và gateway
     */
    private function syncPaymentStatus(DonHang $order, string $status, string $gateway): void
    {
        $statusMap = [
            'paid' => 'thanh_cong',
            'pending' => 'cho',
            'refunded' => 'hoan_tien',
        ];

        $internalStatus = $statusMap[$status] ?? 'cho';
        $allowedGateways = ['momo', 'zalopay', 'paypal', 'ngan_hang', 'tra_sau'];
        $gatewayValue = in_array($gateway, $allowedGateways, true) ? $gateway : 'ngan_hang';

        // Tạo bản ghi thanh toán mới để lưu lịch sử thay đổi trạng thái
        ThanhToan::create([
            'don_hang_id' => $order->id,
            'ma_thanh_toan' => sprintf('ADM-%s', strtoupper(Str::random(6))), // Mã giao dịch admin tự sinh
            'cong_thanh_toan' => $gatewayValue,
            'so_tien' => $order->tong_tien,
            'trang_thai' => $internalStatus,
            'ma_giao_dich' => strtoupper(Str::random(10)),
            'thoi_diem_thanh_toan' => now(),
        ]);
    }

    /**
     * Giải phóng ghế khi hủy đơn: set ghế về 'trong', tăng ghe_con của trip
     * Return: số ghế đã giải phóng
     */
    private function releaseSeats(DonHang $order): int
    {
        // Lấy danh sách ID ghế trong đơn hàng
        $seatIds = $order->chiTietDonHang()->pluck('ghe_id')->filter()->values();
        if ($seatIds->isEmpty()) {
            return 0;
        }

        // Cập nhật trạng thái ghế về 'trong' (available)
        $update = ['trang_thai' => 'trong'];
        if (Schema::hasColumn('ghes', 'ngay_cap_nhat')) {
            $update['ngay_cap_nhat'] = now();
        }

        Ghe::whereIn('id', $seatIds)->update($update);

        // Cập nhật lại số lượng ghế còn trống của chuyến đi
        // Sử dụng lockForUpdate để tránh race condition khi nhiều người cùng đặt/hủy
        $trip = $order->chuyenDi()->lockForUpdate()->first();
        if ($trip) {
            $trip->update([
                'ghe_con' => max(0, (int) ($trip->ghe_con ?? 0) + $seatIds->count()),
                'ngay_cap_nhat' => now(),
            ]);
        }

        return $seatIds->count();
    }

    /**
     * Format datetime cho display (default: d/m/Y H:i)
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
