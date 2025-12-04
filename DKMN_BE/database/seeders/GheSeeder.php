<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Seeder dữ liệu Ghế (Seats)
 * Tạo danh sách ghế cho từng chuyến đi dựa trên số lượng ghế của chuyến
 * Logic đánh số ghế (A1, A2...) và set trạng thái ngẫu nhiên (trống, đã đặt, khóa)
 */
class GheSeeder extends Seeder
{
    public function run(): void
    {


        // We can skip truncate if we assume migrate:fresh was run, 
        // or keep it but handle errors gracefully.
        // Since DatabaseSeeder disables FK checks, we might not need to do it here again,
        // but for safety when running individually:
        Schema::disableForeignKeyConstraints();
        try {
            DB::table('ghes')->truncate();
        } catch (\Exception $e) {
            try {
                DB::table('ghes')->delete();
            } catch (\Exception $e2) {
                // ignore
            }
        }
        Schema::enableForeignKeyConstraints();

        $now = now();

        // Use chunkById to avoid loading all trips into memory
        DB::table('chuyen_dis')
            ->join('nha_van_hanhs', 'chuyen_dis.nha_van_hanh_id', '=', 'nha_van_hanhs.id')
            ->select('chuyen_dis.id', 'chuyen_dis.gia_co_ban', 'chuyen_dis.tong_ghe', 'nha_van_hanhs.loai')
            ->orderBy('chuyen_dis.id')
            ->chunkById(500, function ($trips) use ($now) {
                foreach ($trips as $trip) {
                    $this->processTrip($trip, $now);
                }
            }, 'chuyen_dis.id', 'id');
    }

    private function processTrip($trip, $now)
    {
        $seatCount = (int) $trip->tong_ghe;
        $chunk = [];

        for ($i = 1; $i <= $seatCount; $i++) {
            $status = $this->seatStatus($i);
            $chunk[] = [
                'chuyen_di_id' => $trip->id,
                'so_ghe' => $this->seatLabel($trip->loai, $i),
                'loai_ghe' => $this->seatType($trip->loai),
                'gia' => $trip->gia_co_ban,
                'trang_thai' => $status,
                'ngay_tao' => $now,
            ];

            if (count($chunk) >= 100) {
                DB::reconnect();
                DB::table('ghes')->insert($chunk);
                $chunk = [];
            }
        }

        if (!empty($chunk)) {
            DB::reconnect();
            DB::table('ghes')->insert($chunk);
        }
    }

    private function seatLabel(string $vehicleType, int $index): string
    {
        return $this->twoDeckSeatLabel($index);
    }

    private function twoDeckSeatLabel(int $index): string
    {
        $deckSize = 20;
        $letters = ['A', 'B', 'C', 'D', 'E', 'F'];

        $deck = intdiv(max(0, $index - 1), $deckSize);
        $letter = $letters[$deck] ?? chr(ord('A') + $deck);
        $number = (($index - 1) % $deckSize) + 1;

        return sprintf('%s%d', $letter, $number);
    }

    private function seatType(string $vehicleType): string
    {
        return match ($vehicleType) {
            'may_bay' => 'thuong_gia',
            'tau_hoa' => 'vip',
            default => 'thuong',
        };
    }

    private function seatStatus(int $index): string
    {
        if ($index % 13 === 0) {
            return 'da_dat';
        }

        if ($index % 29 === 0) {
            return 'khoa';
        }

        return 'trong';
    }
}
