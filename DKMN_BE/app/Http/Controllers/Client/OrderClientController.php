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
     * Danh sách đơn hàng của user hiện tại (paginated)
     * Eager load: chuyến đi, trạm, nhà vận hành, ticket, thanh toán
     * Logic:
     * - Lấy user hiện tại từ request
     * - Query đơn hàng thuộc về user đó
     * - Eager load các quan hệ cần thiết để hiển thị thông tin tóm tắt
     * - Sắp xếp đơn mới nhất lên đầu và phân trang
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user('sanctum') ?? $request->user();

        // Query builder với eager loading tối ưu
        $paginator = DonHang::query()
            ->with([
                'chuyenDi.tramDi.tinhThanh', // Load thông tin trạm đi và tỉnh thành
                'chuyenDi.tramDen.tinhThanh', // Load thông tin trạm đến và tỉnh thành
                'chuyenDi.nhaVanHanh', // Load thông tin nhà xe
                'ticket', // Load thông tin vé điện tử
                'thanhToans', // Load lịch sử thanh toán
            ])
            ->where('nguoi_dung_id', $user->id) // Chỉ lấy đơn của user hiện tại
            ->orderByDesc('ngay_tao')
            ->paginate($this->resolvePerPage($request, 10));

        // Transform dữ liệu trả về
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

        // Kiểm tra quyền sở hữu đơn hàng
        if ((int) $donHang->nguoi_dung_id !== (int) $user->id) {
            // Trả về JSON response
        return response()->json([
                'status' => false,
                'message' => 'Không có quyền xem đơn hàng này.',
            ], 403);
        }

        // Load chi tiết các quan hệ sâu hơn cho trang chi tiết
        $donHang->load([
            'chuyenDi.tramDi.tinhThanh',
            'chuyenDi.tramDen.tinhThanh',
            'chuyenDi.nhaVanHanh',
            'chiTietDonHang.ghe', // Load chi tiết ghế ngồi
            'ticket',
            'thanhToans',
        ]);

        // Map danh sách ghế/hành khách
        $items = $donHang->chiTietDonHang->map(function ($item) {
            return [
                'seatId' => $item->ghe_id,
                'seatLabel' => $item->ghe?->so_ghe,
                'passenger' => $item->ten_hanh_khach,
                'phone' => $item->sdt_hanh_khach,
                'price' => (float) $item->gia_ghe,
            ];
        });

        // Transform thông tin chung đơn hàng
        $order = $this->transformOrder($donHang);
        $order['items'] = $items;
        
        // Map lịch sử thanh toán
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
        // Fallback tên trạm từ order nếu trip đã bị xóa hoặc thay đổi
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
        // Lấy trạng thái thanh toán mới nhất
        $latest = $order->thanhToans->first(); // Giả định quan hệ thanhToans đã được sắp xếp mới nhất
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

        // 1. Kiểm tra quyền sở hữu
        if ((int) $donHang->nguoi_dung_id !== (int) $user->id) {
            // Trả về JSON response
        return response()->json([
                'status' => false,
                'message' => 'Không có quyền thao tác trên đơn hàng này.',
            ], 403);
        }

        // 2. Kiểm tra trạng thái đơn hàng (chỉ cho hủy khi chưa hoàn tất/đã đi)
        if (!in_array($donHang->trang_thai, ['cho_xu_ly', 'da_xac_nhan', 'cho_thanh_toan'])) {
            // Trả về JSON response
        return response()->json([
                'status' => false,
                'message' => 'Không thể hủy đơn hàng ở trạng thái hiện tại.',
            ], 422);
        }

        // 3. Thực hiện hủy trong transaction
        \Illuminate\Support\Facades\DB::transaction(function () use ($donHang) {
            // Cập nhật trạng thái đơn hàng
            $donHang->update(['trang_thai' => 'da_huy']);

            // Cập nhật trạng thái vé (nếu có)
            if ($donHang->ticket) {
                $donHang->ticket->update(['status' => \App\Models\Ticket::STATUS_CANCELLED]);
            }

            // Giải phóng ghế ngồi (quan trọng để người khác có thể đặt)
            $donHang->chiTietDonHang->each(function ($detail) {
                if ($detail->ghe) {
                    $detail->ghe->update(['trang_thai' => 'trong']);
                }
            });
            
            // Lưu ý: Có thể cần cập nhật lại số ghế còn (ghe_con) của chuyến đi tại đây hoặc dùng Observer/Event
        });

        // Trả về JSON response
        return response()->json([
            'status' => true,
            'message' => 'Hủy đơn hàng thành công.',
            'data' => $this->transformOrder($donHang->fresh()),
        ]);
    }
}
