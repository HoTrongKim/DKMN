<?php

namespace App\Http\Controllers\Admin;

use App\Exports\PaymentReportExport;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\ThanhToan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Controller quản lý thanh toán và xuất báo cáo cho Admin
 * Xử lý cả thanh toán online (Payment) và manual (ThanhToan)
 * Export Excel với thống kê tổng hợp
 */
class PaymentAdminController extends Controller
{
    /**
     * Danh sách giao dịch có filter: type (online/manual), status, method, dateFrom, dateTo
     * Merge 2 table: payments (online) và thanh_toans (manual)
     * Logic:
     * - Xác định loại giao dịch cần lấy (online hoặc manual) dựa trên input hoặc tình trạng bảng payments
     * - Gọi hàm xử lý riêng cho từng loại (onlinePayments hoặc manualPayments)
     * - Áp dụng các filter về trạng thái, phương thức, thời gian
     */
    /**
     * Danh sách giao dịch có filter: type (online/manual), status, method, dateFrom, dateTo
     * Merge 2 table: payments (online) và thanh_toans (manual)
     * Logic:
     * - Xác định loại giao dịch cần lấy (online hoặc manual) dựa trên input hoặc tình trạng bảng payments
     * - Gọi hàm xử lý riêng cho từng loại (onlinePayments hoặc manualPayments)
     * - Áp dụng các filter về trạng thái, phương thức, thời gian
     */
        /**
     * Lấy danh sách dữ liệu với phân trang và filter
     */
    public function index(Request $request)
    {
        // Validate các tham số lọc đầu vào
        $validated = $request->validate([
            'type' => 'nullable|string|in:online,manual', // Loại thanh toán: online (VNPAY/Momo) hoặc manual (tiền mặt/CK)
            'status' => 'nullable|string|in:pending,success,failed,refunded', // Trạng thái giao dịch
            'method' => 'nullable|string|max:50', // Phương thức thanh toán cụ thể
            'dateFrom' => 'nullable|date', // Ngày bắt đầu lọc
            'dateTo' => 'nullable|date|after_or_equal:dateFrom', // Ngày kết thúc lọc
        ]);

        // Kiểm tra xem hệ thống đã có bảng payments chưa (cho tính năng thanh toán online)
        $hasPayments = $this->hasPaymentsTable();
        // Xác định loại thanh toán mặc định nếu user không chọn
        // Nếu có bảng payments thì ưu tiên hiển thị online, ngược lại hiển thị manual
        $type = $validated['type'] ?? ($hasPayments ? 'online' : 'manual');

        // Điều hướng xử lý dựa trên loại thanh toán
        if ($type === 'manual') {
            return $this->manualPayments($request, $validated);
        }

        // Nếu chọn online nhưng bảng chưa có -> trả về rỗng kèm warning
        if (!$hasPayments) {
            return $this->emptyOnlinePaymentsResponse($request);
        }

        return $this->onlinePayments($request, $validated);
    }

    /**
     * Xuất báo cáo thanh toán ra file Excel
     * Hỗ trợ filter tương tự index + limit row
     * Có summary: tổng giao dịch, tổng tiền, tỷ lệ thành công...
     */
    public function export(Request $request): BinaryFileResponse|JsonResponse
    {
        // Validate tham số export
        $validated = $request->validate([
            'type' => 'nullable|string|in:online,manual',
            'status' => 'nullable|string|in:pending,success,failed,refunded',
            'method' => 'nullable|string|max:50',
            'dateFrom' => 'nullable|date',
            'dateTo' => 'nullable|date|after_or_equal:dateFrom',
            'limit' => 'nullable|integer|min:10|max:10000', // Giới hạn số dòng export để tránh timeout/memory limit
        ]);

        $hasPayments = $this->hasPaymentsTable();
        $type = $validated['type'] ?? ($hasPayments ? 'online' : 'manual');
        $limit = $this->resolveExportLimit($request);

        // Lấy dữ liệu cần export dựa trên loại thanh toán
        if ($type === 'manual') {
            $records = $this->manualPaymentsQuery($validated)->limit($limit)->get();
            // Map dữ liệu raw từ DB sang format row của Excel
            $rows = $this->mapManualExportRows($records);
        } else {
            if (!$hasPayments) {
                // Trả về JSON response
        return response()->json([
                    'status' => false,
                    'message' => 'Bảng payments chưa sẵn sàng. Không thể xuất báo cáo giao dịch online.',
                ], 422);
            }
            $records = $this->onlinePaymentsQuery($validated)->limit($limit)->get();
            $rows = $this->mapOnlineExportRows($records);
        }

        if ($rows->isEmpty()) {
            // Trả về JSON response
        return response()->json([
                'status' => false,
                'message' => 'Không có giao dịch phù hợp để xuất.',
            ], 422);
        }

        $now = Carbon::now(config('app.timezone'));
        // Tính toán các chỉ số tổng hợp (tổng tiền, số lượng thành công/thất bại)
        $summary = $this->summarizeExportRows($rows);

        // Chuẩn bị metadata cho file Excel (tiêu đề, thời gian xuất, filter đã dùng)
        $meta = array_merge($summary, [
            'title' => 'Báo cáo thanh toán & giao dịch',
            'subtitle' => $type === 'manual' ? 'Thanh toán thủ công' : 'Thanh toán online',
            'period' => $this->describePeriod($validated),
            'filters' => $this->describeFilters($validated, $type),
            'generatedAt' => $now,
        ]);

        // Sử dụng thư viện Maatwebsite Excel để tạo file
        $export = new PaymentReportExport($rows, $meta);
        $fileName = sprintf('dkmn-payments-%s.xlsx', $now->format('Ymd_His'));

        return Excel::download($export, $fileName);
    }

    /**
     * Query thanh toán online từ bảng payments (paginated)
     * Transform: lấy order ID, map status label
     */
    private function onlinePayments(Request $request, array $filters)
    {
        // Query và phân trang
        $paginator = $this->onlinePaymentsQuery($filters)->paginate($this->resolvePerPage($request));
        
        // Transform dữ liệu trả về cho API
        $data = $paginator->getCollection()->map(function (Payment $payment) {
            // Lấy thông tin đơn hàng liên quan thông qua ticket
            $order = $payment->ticket?->donHang;

            return [
                'id' => $payment->id,
                'orderId' => $order?->id,
                'amount' => (int) $payment->amount_vnd,
                'method' => $payment->method,
                'provider' => $payment->provider,
                'status' => $payment->status,
                'statusLabel' => $this->mapOnlineStatusLabel($payment->status),
                'paidAt' => optional($payment->paid_at)->toIso8601String(),
            ];
        });

        return $this->respondWithPagination($paginator, $data);
    }

    /**
     * Query thanh toán manual từ bảng thanh_toans (paginated)
     * Transform: lấy order ID, map status label, method = 'MANUAL'
     */
    private function manualPayments(Request $request, array $filters)
    {
        $paginator = $this->manualPaymentsQuery($filters)->paginate($this->resolvePerPage($request));
        $data = $paginator->getCollection()->map(function (ThanhToan $payment) {
            $order = $payment->donHang;

            return [
                'id' => $payment->id,
                'orderId' => $order?->id,
                'amount' => (float) $payment->so_tien,
                'method' => 'MANUAL', // Đánh dấu là thủ công
                'provider' => $payment->cong_thanh_toan, // Ví dụ: tien_mat, chuyen_khoan
                'status' => $payment->trang_thai,
                'statusLabel' => $this->mapManualStatusLabel($payment->trang_thai),
                'paidAt' => optional($payment->thoi_diem_thanh_toan)->toIso8601String(),
            ];
        });

        return $this->respondWithPagination($paginator, $data);
    }

    /**
     * Build query cho payments table (online payments)
     * Filters: status, method, dateFrom, dateTo
     */
    private function onlinePaymentsQuery(array $filters): Builder
    {
        // Eager load ticket và donHang để lấy thông tin đơn hàng liên quan
        $query = Payment::query()->with('ticket.donHang')->orderByDesc('paid_at');

        // Áp dụng các bộ lọc
        if (!empty($filters['status'])) {
            $query->where('status', $this->mapOnlineStatus($filters['status']));
        }

        if (!empty($filters['method'])) {
            $query->where('method', Str::upper($filters['method']));
        }

        if (!empty($filters['dateFrom'])) {
            $query->where('paid_at', '>=', Carbon::parse($filters['dateFrom'])->startOfDay());
        }

        if (!empty($filters['dateTo'])) {
            $query->where('paid_at', '<=', Carbon::parse($filters['dateTo'])->endOfDay());
        }

        return $query;
    }

    /**
     * Build query cho thanh_toans table (manual payments)
     * Filters: status, method, dateFrom, dateTo
     */
    private function manualPaymentsQuery(array $filters): Builder
    {
        $query = ThanhToan::query()->with('donHang')->orderByDesc('thoi_diem_thanh_toan');

        if (!empty($filters['status'])) {
            $query->where('trang_thai', $this->mapManualStatus($filters['status']));
        }

        if (!empty($filters['method'])) {
            $query->where('cong_thanh_toan', $filters['method']);
        }

        if (!empty($filters['dateFrom'])) {
            $query->where('thoi_diem_thanh_toan', '>=', Carbon::parse($filters['dateFrom'])->startOfDay());
        }

        if (!empty($filters['dateTo'])) {
            $query->where('thoi_diem_thanh_toan', '<=', Carbon::parse($filters['dateTo'])->endOfDay());
        }

        return $query;
    }

    /**
     * Map status từ filter input (success/failed/refunded) sang online payment DB status
     */
    private function mapOnlineStatus(string $status): string
    {
        return match ($status) {
            'success' => 'SUCCEEDED',
            'failed' => 'FAILED',
            'refunded' => 'REFUNDED',
            default => 'PENDING',
        };
    }

    /**
     * Map status từ filter input sang manual payment DB status
     */
    private function mapManualStatus(string $status): string
    {
        return match ($status) {
            'success' => 'thanh_cong',
            'refunded' => 'hoan_tien',
            default => 'cho',
        };
    }

    /**
     * Map online payment status sang label tiếng Việt
     */
    private function mapOnlineStatusLabel(?string $status): string
    {
        return match ($status) {
            'SUCCEEDED' => 'Thành công',
            'FAILED' => 'Thất bại',
            'REFUNDED' => 'Đã hoàn',
            'EXPIRED' => 'Hết hạn',
            default => 'Đang chờ',
        };
    }

    /**
     * Map manual payment status sang label tiếng Việt
     */
    private function mapManualStatusLabel(?string $status): string
    {
        return match ($status) {
            'thanh_cong' => 'Thành công',
            'hoan_tien' => 'Hoàn tiền',
            default => 'Đang chờ',
        };
    }

    /**
     * Trả về response rỗng khi bảng payments chưa tồn tại
     */
    private function emptyOnlinePaymentsResponse(Request $request): JsonResponse
    {
        $perPage = $this->resolvePerPage($request);

        // Trả về JSON response
        return response()->json([
            'data' => [],
            'meta' => [
                'currentPage' => 1,
                'lastPage' => 1,
                'perPage' => $perPage,
                'total' => 0,
            ],
            'warning' => 'Bảng payments chưa sẵn sàng. Vui lòng chạy migrate để kích hoạt dữ liệu giao dịch trực tuyến.',
        ]);
    }

    /**
     * Transform online payments thành rows cho Excel export
     */
    private function mapOnlineExportRows(Collection $payments): Collection
    {
        return $payments->map(function (Payment $payment) {
            $order = $payment->ticket?->donHang;
            $statusKey = $this->statusKeyFromOnline($payment->status);

            return [
                'code' => sprintf('ONL-%06d', $payment->id),
                'orderCode' => $this->formatOrderCode($order?->id),
                'typeLabel' => $statusKey === 'refunded' ? 'Hoàn tiền' : 'Thanh toán',
                'gateway' => sprintf('%s / %s', Str::upper($payment->method ?? '—'), Str::upper($payment->provider ?? '—')),
                'statusLabel' => $this->mapOnlineStatusLabel($payment->status),
                'statusKey' => $statusKey,
                'amount' => (int) $payment->amount_vnd,
                'time' => $payment->paid_at ? $payment->paid_at->copy() : null,
                'note' => $payment->provider_ref ? 'Mã tham chiếu: ' . $payment->provider_ref : '',
            ];
        });
    }

    /**
     * Transform manual payments thành rows cho Excel export
     */
    private function mapManualExportRows(Collection $payments): Collection
    {
        return $payments->map(function (ThanhToan $payment) {
            $order = $payment->donHang;
            $statusKey = $this->statusKeyFromManual($payment->trang_thai);

            return [
                'code' => sprintf('MAN-%06d', $payment->id),
                'orderCode' => $this->formatOrderCode($order?->id),
                'typeLabel' => $statusKey === 'refunded' ? 'Hoàn tiền' : 'Thanh toán',
                'gateway' => sprintf('Thủ công / %s', Str::upper($payment->cong_thanh_toan ?? '—')),
                'statusLabel' => $this->mapManualStatusLabel($payment->trang_thai),
                'statusKey' => $statusKey,
                'amount' => (float) $payment->so_tien,
                'time' => $payment->thoi_diem_thanh_toan ? Carbon::parse($payment->thoi_diem_thanh_toan) : null,
                'note' => $payment->ma_thanh_toan ? 'Mã thanh toán: ' . $payment->ma_thanh_toan : '',
            ];
        });
    }

    /**
     * Tính tổng summary từ export rows: tổng tiền, tổng số, count theo status
     */
    private function summarizeExportRows(Collection $rows): array
    {
        return [
            'totalAmount' => (float) $rows->sum('amount'),
            'totalCount' => $rows->count(),
            'successCount' => $rows->where('statusKey', 'success')->count(),
            'refundedCount' => $rows->where('statusKey', 'refunded')->count(),
            'failedCount' => $rows->where('statusKey', 'failed')->count(),
        ];
    }

    /**
     * Convert online payment status sang status key (success/refunded/failed/pending)
     */
    private function statusKeyFromOnline(?string $status): string
    {
        return match ($status) {
            'SUCCEEDED' => 'success',
            'REFUNDED' => 'refunded',
            'FAILED', 'EXPIRED' => 'failed',
            default => 'pending',
        };
    }

    /**
     * Convert manual payment status sang status key (success/refunded/pending)
     */
    private function statusKeyFromManual(?string $status): string
    {
        return match ($status) {
            'thanh_cong' => 'success',
            'hoan_tien' => 'refunded',
            default => 'pending',
        };
    }

    /**
     * Resolve limit cho export (min: 100, max: 10000, default: 2000)
     */
    private function resolveExportLimit(Request $request): int
    {
        $limit = (int) $request->input('limit', 2000);

        return max(100, min(10000, $limit));
    }

    /**
     * Mô tả filters đang áp dụng (type, method, status) cho Excel
     */
    private function describeFilters(array $filters, string $type): string
    {
        $parts = [
            'Loại: ' . ($type === 'manual' ? 'Thanh toán thủ công' : 'Thanh toán online'),
        ];

        if (!empty($filters['method'])) {
            $parts[] = 'Phương thức: ' . Str::upper($filters['method']);
        }

        if (!empty($filters['status'])) {
            $parts[] = 'Trạng thái: ' . $this->filterStatusLabel($filters['status']);
        }

        return implode(' | ', array_filter($parts)) ?: 'Không áp dụng bộ lọc bổ sung';
    }

    /**
     * Mô tả khoảng thời gian filter (dateFrom → dateTo) cho Excel
     */
    private function describePeriod(array $filters): string
    {
        $from = $filters['dateFrom'] ?? null;
        $to = $filters['dateTo'] ?? null;

        if ($from && $to) {
            return sprintf('%s → %s', $this->formatDateLabel($from), $this->formatDateLabel($to));
        }

        if ($from) {
            return sprintf('Từ %s đến nay', $this->formatDateLabel($from));
        }

        if ($to) {
            return sprintf('Đến %s', $this->formatDateLabel($to));
        }

        return 'Toàn bộ thời gian';
    }

    /**
     * Format date label cho display (d/m/Y)
     */
    private function formatDateLabel(string $value): string
    {
        return Carbon::parse($value)->format('d/m/Y');
    }

    /**
     * Map filter status input sang label tiếng Việt
     */
    private function filterStatusLabel(string $status): string
    {
        return match ($status) {
            'success' => 'Thành công',
            'failed' => 'Thất bại',
            'refunded' => 'Hoàn tiền',
            default => 'Đang chờ',
        };
    }

    /**
     * Format order code cho display (ORD-000123 hoặc "—" nếu null)
     */
    private function formatOrderCode(?int $orderId): string
    {
        if (!$orderId) {
            return '—';
        }

        return sprintf('DH-%05d', $orderId);
    }
}
