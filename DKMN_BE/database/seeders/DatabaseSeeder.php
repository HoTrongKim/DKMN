<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Seeder chính của hệ thống
 * Chịu trách nhiệm gọi các seeder con theo đúng thứ tự để đảm bảo ràng buộc dữ liệu
 * Thực hiện truncate/delete dữ liệu cũ trước khi seed mới
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tắt kiểm tra khóa ngoại để có thể truncate/delete dữ liệu thoải mái
        Schema::disableForeignKeyConstraints();
        $this->prepareTables();

        $this->call([
            // Gọi các seeder con theo thứ tự logic (từ điển -> master data -> transaction data)
            TinhThanhSeeder::class,
            TramSeeder::class,
            NhaVanHanhSeeder::class,
            NguoiDungSeeder::class,
            QuyenHanSeeder::class,
            NguoiDungQuyenHanSeeder::class,
            ChuyenDiSeeder::class,
            GheSeeder::class,
            DonHangSeeder::class,
            ChiTietDonHangSeeder::class,
            PhiDichVuSeeder::class,
            ChiTietPhiDonHangSeeder::class,
            TicketSeeder::class,
            ThanhToanSeeder::class,
            PaymentSeeder::class,
            CauHinhHeThongSeeder::class,
            ThongKeDoanhThuSeeder::class,
        ]);

        Schema::enableForeignKeyConstraints();
    }

    private function prepareTables(): void
    {
        $truncateTables = [
            'payments',
            'tickets',
            'chi_tiet_phi_don_hangs',
            'chi_tiet_don_hangs',
            'thanh_toans',
            'huy_ves',
            'danh_gias',
            'phan_hois',
            'thong_baos',
            'nhat_ky_hoat_dongs',
            'nguoi_dung_quyen_hans',
        ];

        foreach ($truncateTables as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->truncate();
            }
        }

        $deleteTables = [
            'don_hangs',
            'ghes',
            'phi_dich_vus',
            'chuyen_dis',
            'nguoi_dungs',
            'quyen_hans',
            'nha_van_hanhs',
            'trams',
            'tinh_thanhs',
        ];

        foreach ($deleteTables as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->delete();
            }
        }

        $finalTruncate = [
            'thong_ke_doanh_thus',
            'cau_hinh_he_thongs',
        ];

        foreach ($finalTruncate as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->truncate();
            }
        }
    }
}
