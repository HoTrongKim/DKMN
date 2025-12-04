<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChuyenDi;
use App\Models\DanhGia;
use App\Models\DonHang;
use App\Models\NguoiDung;
use App\Models\Payment;
use App\Models\ThanhToan;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

/**
 * Controller quản lý dashboard và thống kê tổng quan cho Admin
 * Xử lý các API trả về dữ liệu dashboard, báo cáo theo kỳ
 */
class DashboardAdminController extends Controller
{
    /**
     * Lấy dữ liệu tổng quan dashboard
     * Trả về: số liệu hôm nay, tổng vé/doanh thu, đơn gần đây, tuyến hot, doanh thu 6 tháng
     * Logic:
     * - Tính toán số liệu thống kê cơ bản (count trips, orders, customers)
     * - Tính doanh thu hôm nay và hôm qua để so sánh (delta)
     * - Lấy danh sách đơn hàng mới nhất và tuyến đường được đặt nhiều nhất
     * - Tổng hợp doanh thu 6 tháng gần nhất (gộp cả thanh toán online và thủ công)
     */
    /**
     * Lấy dữ liệu tổng quan dashboard
     * Trả về: số liệu hôm nay, tổng vé/doanh thu, đơn gần đây, tuyến hot, doanh thu 6 tháng
     * Logic:
     * - Tính toán số liệu thống kê cơ bản (count trips, orders, customers)
     * - Tính doanh thu hôm nay và hôm qua để so sánh (delta)
     * - Lấy danh sách đơn hàng mới nhất và tuyến đường được đặt nhiều nhất
     * - Tổng hợp doanh thu 6 tháng gần nhất (gộp cả thanh toán online và thủ công)
     */
    public function overview()
    {
        // Khởi tạo mốc thời gian hôm nay và hôm qua để so sánh dữ liệu
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        // Đếm tổng số lượng các thực thể chính trong hệ thống
        $totalTrips = ChuyenDi::count(); // Tổng số chuyến đi
        $totalOrders = DonHang::count(); // Tổng số đơn hàng
        $totalCustomers = NguoiDung::where('vai_tro', 'khach_hang')->count(); // Tổng số khách hàng (loại trừ admin/nhà xe)

        // Kiểm tra xem bảng payments có tồn tại không để tính doanh thu online
        // Việc này giúp tránh lỗi SQL nếu hệ thống chưa chạy migration cho bảng payments
        $hasPayments = $this->hasPaymentsTable();

        // Tính tổng doanh thu từ thanh toán thủ công (tiền mặt/chuyển khoản trực tiếp)
        // Chỉ tính các giao dịch có trạng thái 'thanh_cong'
        $manualRevenue = ThanhToan::where('trang_thai', 'thanh_cong')->sum('so_tien');

        // Tính tổng doanh thu từ thanh toán online (VNPAY/Momo...) nếu bảng tồn tại
        // Chỉ tính các giao dịch có status 'succeeded' (đã thanh toán thành công)
        $onlineRevenue = $hasPayments
            ? Payment::where('status', Payment::STATUS_SUCCEEDED)->sum('amount_vnd')
            : 0;

        // Đếm số vé bán ra hôm nay và hôm qua (dựa trên ngày tạo đơn hàng)
        $ticketsToday = DonHang::whereDate('ngay_tao', $today)->count();
        $ticketsYesterday = DonHang::whereDate('ngay_tao', $yesterday)->count();

        // Tính doanh thu chi tiết cho hôm nay và hôm qua (gộp cả online và manual)
        // Dùng hàm helper dailyRevenue để tái sử dụng logic
        $revenueToday = $this->dailyRevenue($today, $hasPayments);
        $revenueYesterday = $this->dailyRevenue($yesterday, $hasPayments);

        // Đếm số khách hàng mới đăng ký trong 7 ngày qua
        // Giúp theo dõi tốc độ tăng trưởng người dùng
        $newCustomers = NguoiDung::where('vai_tro', 'khach_hang')
            ->where('ngay_tao', '>=', Carbon::now()->subDays(7))
            ->count();

        // Tính điểm đánh giá trung bình của toàn hệ thống
        // Chỉ tính các đánh giá đã được duyệt (trang_thai = 'chap_nhan')
        // Làm tròn đến 1 chữ số thập phân
        $ratingScore = (float) round(
            DanhGia::where('trang_thai', 'chap_nhan')->avg('diem') ?? 0,
            1
        );

        // Lấy danh sách 5 đơn hàng mới nhất để hiển thị widget "Đơn hàng gần đây"
        // Eager load quan hệ 'nguoiDung' để lấy tên khách hàng, tránh N+1 query
        $recentOrders = DonHang::query()
            ->with('nguoiDung')
            ->orderByDesc('ngay_tao')
            ->limit(5)
            ->get()
            ->map(function (DonHang $order) {
                // Map dữ liệu trả về format FE cần
                return [
                    'id' => $order->id,
                    'code' => $order->ma_don,
                    // Ưu tiên lấy tên từ quan hệ user, nếu không có (khách vãng lai) thì lấy tên khách trong đơn
                    'customer' => $order->nguoiDung?->ho_ten ?? $order->ten_khach,
                    'total' => (float) $order->tong_tien,
                    'status' => $order->trang_thai,
                    'createdAt' => optional($order->ngay_tao)->toIso8601String(),
                ];
            });

        // Thống kê 5 tuyến đường phổ biến nhất dựa trên số lượng đơn hàng
        // Sử dụng GROUP BY theo nơi đi và nơi đến
        $topRoutes = DonHang::query()
            ->selectRaw('CONCAT(COALESCE(noi_di, "Unknown"), " → ", COALESCE(noi_den, "Unknown")) as route, COUNT(*) as total')
            ->groupBy('route')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // Lấy dữ liệu biểu đồ doanh thu 6 tháng gần nhất
        $monthlyRevenue = $this->monthlyRevenue($hasPayments);

        // Trả về JSON response
        return response()->json([
            'status' => true,
            'data' => [
                'summary' => [
                    'ticketsToday' => $ticketsToday,
                    'revenueToday' => (float) $revenueToday,
                    'newCustomers' => $newCustomers,
                    'ratingScore' => $ratingScore,
                    // Tính delta (tăng/giảm) so với hôm qua để hiển thị xu hướng
                    'ticketsTodayDelta' => $ticketsToday - $ticketsYesterday,
                    'revenueTodayDelta' => (float) ($revenueToday - $revenueYesterday),
                    'ratingBase' => 5,
                ],
                'counters' => [
                    'trips' => $totalTrips,
                    'orders' => $totalOrders,
                    'customers' => $totalCustomers,
                    'revenue' => (float) ($manualRevenue + $onlineRevenue),
                ],
                'recentOrders' => $recentOrders,
                'topRoutes' => $topRoutes,
                'monthlyRevenue' => $monthlyRevenue,
            ],
        ]);
    }

    /**
     * Tính doanh thu theo tháng (6 tháng gần nhất)
     * Gộp doanh thu thủ công (thanh_toans) và online (payments nếu có)
     */
    private function monthlyRevenue(bool $hasPaymentsTable): array
    {
        // Xác định mốc thời gian bắt đầu (đầu tháng của 5 tháng trước)
        $from = Carbon::now()->subMonths(5)->startOfMonth();

        // Query doanh thu thủ công theo tháng
        // Group by format YYYY-MM
        $manual = ThanhToan::query()
            ->selectRaw('DATE_FORMAT(thoi_diem_thanh_toan, "%Y-%m") as month, SUM(so_tien) as total')
            ->where('trang_thai', 'thanh_cong')
            ->where('thoi_diem_thanh_toan', '>=', $from)
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        // Query doanh thu online theo tháng (nếu có bảng payments)
        $online = $hasPaymentsTable
            ? Payment::query()
                ->selectRaw('DATE_FORMAT(paid_at, "%Y-%m") as month, SUM(amount_vnd) as total')
                ->where('status', Payment::STATUS_SUCCEEDED)
                ->where('paid_at', '>=', $from)
                ->groupBy('month')
                ->pluck('total', 'month')
                ->toArray()
            : [];

        // Tổng hợp dữ liệu cho 6 tháng liên tiếp (bao gồm cả tháng hiện tại)
        // Đảm bảo tháng nào không có doanh thu vẫn hiển thị là 0
        $months = [];
        for ($i = 0; $i < 6; $i++) {
            $key = $from->copy()->addMonths($i)->format('Y-m');
            // Cộng dồn doanh thu thủ công và online
            $months[$key] = ($manual[$key] ?? 0) + ($online[$key] ?? 0);
        }

        // Chuyển đổi format mảng trả về cho FE (dạng list object)
        return collect($months)->map(fn ($value, $month) => [
            'month' => $month,
            'total' => (float) $value,
        ])->values()->toArray();
    }

    /**
     * Tạo báo cáo chi tiết theo kỳ (tuần/tháng/quý/năm)
     * Bao gồm: tổng vé, doanh thu, tỷ lệ hủy, đánh giá trung bình, tuyến hot, nhà vận hành tốt nhất
     */
    public function report(Request $request)
    {
        // Validate tham số period đầu vào
        $validated = $request->validate([
            'period' => ['nullable', Rule::in(['week', 'month', 'quarter', 'year'])],
        ]);

        $period = $validated['period'] ?? 'month';
        // Tính toán khoảng thời gian start/end dựa trên period
        [$start, $end] = $this->resolvePeriodRange($period);

        // Query cơ sở cho đơn hàng trong khoảng thời gian
        $ordersInRange = DonHang::query()->whereBetween('ngay_tao', [$start, $end]);

        // Sử dụng clone để tái sử dụng query builder cho các thống kê khác nhau
        // 1. Tổng số đơn hàng
        $totalOrders = (clone $ordersInRange)->count();
        // 2. Tổng số vé (nếu so_hanh_khach null thì tính là 1)
        $totalTickets = (clone $ordersInRange)->sum(DB::raw('COALESCE(so_hanh_khach, 1)'));
        // 3. Số đơn hàng bị hủy
        $cancelledOrders = (clone $ordersInRange)->where('trang_thai', 'da_huy')->count();

        $hasPayments = $this->hasPaymentsTable();

        // Tính doanh thu thủ công trong kỳ
        $manualRevenue = ThanhToan::query()
            ->where('trang_thai', 'thanh_cong')
            ->whereBetween('thoi_diem_thanh_toan', [$start, $end])
            ->sum('so_tien');

        // Tính doanh thu online trong kỳ
        $onlineRevenue = $hasPayments
            ? Payment::query()
                ->where('status', Payment::STATUS_SUCCEEDED)
                ->whereBetween('paid_at', [$start, $end])
                ->sum('amount_vnd')
            : 0;

        // Query cơ sở cho đánh giá trong kỳ
        $ratingQuery = DanhGia::query()
            ->where('trang_thai', 'chap_nhan')
            ->whereBetween('ngay_tao', [$start, $end]);

        // Tính điểm đánh giá trung bình và tổng số đánh giá
        $averageRating = round((float) ((clone $ratingQuery)->avg('diem') ?? 0), 1);
        $totalReviews = (clone $ratingQuery)->count();

        // Thống kê 5 tuyến đường hot nhất trong kỳ
        $topRoutes = DonHang::query()
            ->selectRaw('COALESCE(noi_di, "Khong ro") as from_location, COALESCE(noi_den, "Khong ro") as to_location, COUNT(*) as total')
            ->whereBetween('ngay_tao', [$start, $end])
            ->groupBy('from_location', 'to_location')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(fn ($row) => [
                'name' => trim(($row->from_location ?? 'Khong ro') . ' -> ' . ($row->to_location ?? 'Khong ro')),
                'tickets' => (int) $row->total,
            ]);

        // Thống kê 5 nhà vận hành được đánh giá cao nhất trong kỳ
        // Join bảng danh_gias -> chuyen_dis -> nha_van_hanhs
        $topCompanies = DanhGia::query()
            ->selectRaw('nha_van_hanhs.ten as name, AVG(danh_gias.diem) as avg_rating, COUNT(*) as reviews')
            ->join('chuyen_dis', 'danh_gias.chuyen_di_id', '=', 'chuyen_dis.id')
            ->join('nha_van_hanhs', 'chuyen_dis.nha_van_hanh_id', '=', 'nha_van_hanhs.id')
            ->where('danh_gias.trang_thai', 'chap_nhan')
            ->whereBetween('danh_gias.ngay_tao', [$start, $end])
            ->groupBy('nha_van_hanhs.id', 'nha_van_hanhs.ten')
            ->orderByDesc('avg_rating')
            ->limit(5)
            ->get()
            ->map(fn ($row) => [
                'name' => $row->name,
                'rating' => (float) round($row->avg_rating ?? 0, 2),
                'reviews' => (int) $row->reviews,
            ]);

        $totalRevenue = (float) ($manualRevenue + $onlineRevenue);
        // Tính tỷ lệ hủy đơn (cancelled / total * 100)
        $cancellationRate = $totalOrders > 0
            ? round(($cancelledOrders / $totalOrders) * 100, 2)
            : 0.0;

        // Trả về JSON response
        return response()->json([
            'status' => true,
            'data' => [
                'period' => $period,
                'range' => [
                    'from' => $start->toDateString(),
                    'to' => $end->toDateString(),
                ],
                'totalRevenue' => $totalRevenue,
                'totalTickets' => (int) $totalTickets,
                'cancellationRate' => $cancellationRate,
                'averageRating' => $averageRating,
                'totalReviews' => (int) $totalReviews,
                'topRoutes' => $topRoutes,
                'topCompanies' => $topCompanies,
            ],
        ]);
    }

    /**
     * Kiểm tra xem bảng payments (thanh toán online) có tồn tại và đầy đủ cột không
     * Cache kết quả để tránh query nhiều lần
     */
    protected function hasPaymentsTable(): bool
    {
        static $cache = null;

        if ($cache !== null) {
            return $cache;
        }

        try {
            // Kiểm tra schema DB xem có bảng payments và các cột cần thiết không
            $cache = Schema::hasTable('payments')
                && Schema::hasColumns('payments', ['status', 'amount_vnd', 'paid_at']);
        } catch (\Throwable $exception) {
            report($exception);
            $cache = false;
        }

        return $cache;
    }

    /**
     * Chuyển đổi period (week/month/quarter/year) thành khoảng thời gian Carbon
     * Trả về [start, end] để filter dữ liệu
     */
    private function resolvePeriodRange(string $period): array
    {
        $end = Carbon::now()->endOfDay();

        // Tính toán ngày bắt đầu dựa trên loại kỳ báo cáo
        $start = match ($period) {
            'week' => $end->copy()->subDays(6)->startOfDay(), // 7 ngày gần nhất
            'quarter' => $end->copy()->firstOfQuarter()->startOfDay(), // Đầu quý hiện tại
            'year' => $end->copy()->startOfYear()->startOfDay(), // Đầu năm hiện tại
            default => $end->copy()->startOfMonth()->startOfDay(), // Đầu tháng hiện tại (mặc định)
        };

        return [$start, $end];
    }

    /**
     * Tính tổng doanh thu trong một ngày cụ thể
     * Gộp manual (thanh_toans) và online (payments nếu có)
     */
    private function dailyRevenue(Carbon $date, bool $hasPaymentsTable): float
    {
        // Doanh thu từ thanh toán thủ công trong ngày
        $manual = ThanhToan::where('trang_thai', 'thanh_cong')
            ->whereDate('thoi_diem_thanh_toan', $date)
            ->sum('so_tien');

        // Doanh thu từ thanh toán online trong ngày
        $online = $hasPaymentsTable
            ? Payment::where('status', Payment::STATUS_SUCCEEDED)
                ->whereDate('paid_at', $date)
                ->sum('amount_vnd')
            : 0;

        return (float) ($manual + $online);
    }
}
