<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\DonHang;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * Controller quản lý đơn hàng cho khách hàng
 * Xem danh sách đơn hàng, chi tiết đơn, hủy đơn
 * Kiểm tra ownership để đảm bảo chỉ user tạo đơn mới xem/hủy được
 */
class OrderClientController extends Controller
{
    /**
     * Danh sách đơn hàng của user hiện tại (paginated)
     * Eager load: chuyến đi, trạm, nhà vận hành, ticket, thanh toán
     */
        /**
     * Lấy danh sách dữ liệu với phân trang và filter
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user('sanctum') ?? $request->user();

        $paginator = DonHang::query()
            ->with([
                'chuyenDi.tramDi.tinhThanh',
                'chuyenDi.tramDen.tinhThanh',
                'chuyenDi.nhaVanHanh',
                'ticket',
                'thanhToans',
            ])
            ->where('nguoi_dung_id', $user->id)
            ->orderByDesc('ngay_tao')
            ->paginate($this->resolvePerPage($request, 10));

        $data = $paginator->getCollection()->map(fn (DonHang $order) => $this->transformOrder($order));

        return $this->respondWithPagination($paginator, $data);
    }

    /**
     * Chi tiết đơn hàng: order info + items (passengers/seats) + payments
     * Kiểm tra ownership trước khi trả dữ liệu
     */
        /**
     * Lấy chi tiết một bản ghi theo ID
     */
    public function show(Request $request, DonHang $donHang): JsonResponse
    {
        $user = $request->user('sanctum') ?? $request->user();

        if ((int) $donHang->nguoi_dung_id !== (int) $user->id) {
            // Trả về JSON response
        return response()->json([
                'status' => false,
                'message' => 'Không có quyền xem đơn hàng này.',
            ], 403);
        }

        $donHang->load([
            'chuyenDi.tramDi.tinhThanh',
            'chuyenDi.tramDen.tinhThanh',
            'chuyenDi.nhaVanHanh',
            'chiTietDonHang.ghe',
            'ticket',
            'thanhToans',
        ]);

        $items = $donHang->chiTietDonHang->map(function ($item) {
            return [
                'seatId' => $item->ghe_id,
                'seatLabel' => $item->ghe?->so_ghe,
                'passenger' => $item->ten_hanh_khach,
                'phone' => $item->sdt_hanh_khach,
                'price' => (float) $item->gia_ghe,
            ];
        });

        $order = $this->transformOrder($donHang);
        $order['items'] = $items;
        $order['payments'] = $donHang->thanhToans->map(function ($payment) {
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
     * Transform order object sang format API response
     * Lấy thông tin: trip, customer, status, payment status
     */
    private function transformOrder(DonHang $order): array
    {
        $trip = $order->chuyenDi;
        $from = $trip?->tramDi?->ten ?? $order->noi_di;
        $to = $trip?->tramDen?->ten ?? $order->noi_den;

        return [
            'id' => $order->id,
            'code' => $order->ma_don,
            'status' => $this->mapStatusLabel($order->trang_thai),
            'rawStatus' => $order->trang_thai,
            'paymentStatus' => $this->mapPaymentStatus($order),
            'total' => (float) $order->tong_tien,
            'trip' => [
                'id' => $order->chuyen_di_id,
                'from' => $from,
                'to' => $to,
                'departureTime' => $trip?->gio_khoi_hanh
                    ? Carbon::parse($trip->gio_khoi_hanh)->toIso8601String()
                    : null,
                'arrivalTime' => $trip?->gio_den
                    ? Carbon::parse($trip->gio_den)->toIso8601String()
                    : null,
                'operator' => $trip?->nhaVanHanh?->ten,
            ],
            'createdAt' => optional($order->ngay_tao)->toIso8601String(),
        ];
    }

    /**
     * Map order status sang label tiếng Việt
     */
    private function mapStatusLabel(?string $status): string
    {
        return match ($status) {
            'cho_xu_ly' => 'Đang xử lý',
            'da_xac_nhan' => 'Đã xác nhận',
            'hoan_tat' => 'Hoàn tất',
            'da_huy' => 'Đã huỷ',
            default => 'Không xác định',
        };
    }

    /**
     * Resolve payment status từ latest ThanhToan record
     */
    private function mapPaymentStatus(DonHang $order): string
    {
        $latest = $order->thanhToans->first();
        $status = $latest?->trang_thai;

        return match ($status) {
            'thanh_cong' => 'Đã thanh toán',
            'hoan_tien' => 'Đã hoàn tiền',
            'that_bai' => 'Thanh toán thất bại',
            default => 'Chưa thanh toán',
        };
    }

    /**
     * Hủy đơn hàng của user
     * Kiểm tra ownership và status (chỉ cho phép hủy nếu chưa đi/chưa hoàn tất)
     * Giải phóng ghế và cập nhật số ghế còn của chuyến
     */
    public function cancel(Request $request, DonHang $donHang): JsonResponse
    {
        $user = $request->user('sanctum') ?? $request->user();

        if ((int) $donHang->nguoi_dung_id !== (int) $user->id) {
            // Trả về JSON response
        return response()->json([
                'status' => false,
                'message' => 'Không có quyền thao tác trên đơn hàng này.',
            ], 403);
        }

        if (!in_array($donHang->trang_thai, ['cho_xu_ly', 'da_xac_nhan', 'cho_thanh_toan'])) {
            // Trả về JSON response
        return response()->json([
                'status' => false,
                'message' => 'Không thể hủy đơn hàng ở trạng thái hiện tại.',
            ], 422);
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($donHang) {
            // 1. Update Order status
            $donHang->update(['trang_thai' => 'da_huy']);

            // 2. Update Ticket status
            if ($donHang->ticket) {
                $donHang->ticket->update(['status' => \App\Models\Ticket::STATUS_CANCELLED]);
            }

            // 3. Release Seats
            $donHang->chiTietDonHang->each(function ($detail) {
                if ($detail->ghe) {
                    $detail->ghe->update(['trang_thai' => 'trong']);
                }
            });
        });

        // Trả về JSON response
        return response()->json([
            'status' => true,
            'message' => 'Hủy đơn hàng thành công.',
            'data' => $this->transformOrder($donHang->fresh()),
        ]);
    }
}
