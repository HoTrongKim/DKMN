<?php

namespace Database\Seeders;

use App\Services\RevenueStatisticBuilder;
use Illuminate\Database\Seeder;

/**
 * Seeder Thống kê doanh thu
 * Gọi service RevenueStatisticBuilder để tính toán lại bảng thống kê
 * dựa trên dữ liệu đơn hàng đã seed
 */
class ThongKeDoanhThuSeeder extends Seeder
{
    public function run(): void
    {
        app(RevenueStatisticBuilder::class)->rebuild();
    }
}
