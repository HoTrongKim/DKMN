<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TinhThanhSeeder extends Seeder
{
    public function run(): void
    {
        $cities = [
            ['ten' => 'Hà Nội', 'ma' => 'HN'],
            ['ten' => 'Hải Phòng', 'ma' => 'HP'],
            ['ten' => 'Quảng Ninh', 'ma' => 'QN'],
            ['ten' => 'Bắc Ninh', 'ma' => 'BN'],
            ['ten' => 'Hưng Yên', 'ma' => 'HY'],
            ['ten' => 'Hà Nam', 'ma' => 'HNA'],
            ['ten' => 'Nam Định', 'ma' => 'ND'],
            ['ten' => 'Ninh Bình', 'ma' => 'NB'],
            ['ten' => 'Thanh Hóa', 'ma' => 'TH'],
            ['ten' => 'Nghệ An', 'ma' => 'NA'],
            ['ten' => 'Hà Tĩnh', 'ma' => 'HT'],
            ['ten' => 'Quảng Bình', 'ma' => 'QB'],
            ['ten' => 'Quảng Trị', 'ma' => 'QT'],
            ['ten' => 'Thừa Thiên Huế', 'ma' => 'TTH'],
            ['ten' => 'Đà Nẵng', 'ma' => 'DN'],
            ['ten' => 'Quảng Nam', 'ma' => 'QNA'],
            ['ten' => 'Quảng Ngãi', 'ma' => 'QNG'],
            ['ten' => 'Bình Định', 'ma' => 'BDI'],
            ['ten' => 'Phú Yên', 'ma' => 'PY'],
            ['ten' => 'Khánh Hòa', 'ma' => 'KH'],
            ['ten' => 'Ninh Thuận', 'ma' => 'NT'],
            ['ten' => 'Bình Thuận', 'ma' => 'BT'],
            ['ten' => 'Kon Tum', 'ma' => 'KT'],
            ['ten' => 'Gia Lai', 'ma' => 'GL'],
            ['ten' => 'Đắk Lắk', 'ma' => 'DLK'],
            ['ten' => 'Đắk Nông', 'ma' => 'DNO'],
            ['ten' => 'Lâm Đồng', 'ma' => 'LD'],
            ['ten' => 'Bình Phước', 'ma' => 'BP'],
            ['ten' => 'Bình Dương', 'ma' => 'BD'],
            ['ten' => 'Đồng Nai', 'ma' => 'DNA'],
            ['ten' => 'Bà Rịa - Vũng Tàu', 'ma' => 'BRVT'],
            ['ten' => 'TP. Hồ Chí Minh', 'ma' => 'HCM'],
            ['ten' => 'Long An', 'ma' => 'LA'],
            ['ten' => 'Cần Thơ', 'ma' => 'CT'],
        ];

        DB::table('tinh_thanhs')->delete();

        $now = now();
        $payload = [];
        foreach ($cities as $index => $city) {
            $payload[] = [
                'id' => $index + 1,
                'ten' => $city['ten'],
                'ma' => $city['ma'],
                'ngay_tao' => $now,
            ];
        }

        DB::table('tinh_thanhs')->insert($payload);
    }
}
