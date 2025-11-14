<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ChuyenDiSeeder extends Seeder
{
    private const MIN_TRIP_PRICE = 1000;
    private const MAX_TRIP_PRICE = 2000;
    private const PRICE_VARIANCE_STEP = 50;

    private ?int $demoTripPriceOverride = null;
    private bool $demoTripPriceResolved = false;

    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('chuyen_dis')->delete();
        Schema::enableForeignKeyConstraints();

        $operators = $this->buildLookupMap(
            DB::table('nha_van_hanhs')->get(['id', 'ten'])
        );

        $stations = $this->buildStationLookup(
            DB::table('trams')->get(['id', 'ten', 'tinh_thanh_id'])
        );

        $busRoutes = [
            [
                'operator' => 'Phuong Trang (Futa)',
                'from' => 'Bến xe Giáp Bát',
                'to' => 'Bến xe Miền Đông',
                'price' => 1200,
                'seats' => 44,
                'duration_minutes' => 22 * 60,
                'price_variance' => 6,
            ],
            [
                'operator' => 'Thanh Buoi',
                'from' => 'Bến xe Giáp Bát',
                'to' => 'Bến xe Miền Đông Mới',
                'price' => 1350,
                'seats' => 46,
                'duration_minutes' => 23 * 60,
                'time_slots' => ['07:30', '11:00', '15:30', '19:45'],
                'price_variance' => 5,
            ],
            [
                'operator' => 'Mai Linh',
                'from' => 'Bến xe Miền Đông',
                'to' => 'Bến xe Đà Lạt',
                'price' => 1100,
                'seats' => 40,
                'duration_minutes' => 9 * 60,
                'time_slots' => ['06:15', '09:45', '13:10', '21:00'],
                'price_variance' => 4,
            ],
            [
                'operator' => 'Kumho Samco',
                'from' => 'Bến xe Miền Đông',
                'to' => 'Bến xe Quy Nhơn',
                'price' => 1250,
                'seats' => 42,
                'duration_minutes' => 10 * 60,
                'price_variance' => 5,
            ],
            [
                'operator' => 'Hoang Long',
                'from' => 'Bến xe Nước Ngầm',
                'to' => 'Bến xe Vũng Tàu',
                'price' => 1400,
                'seats' => 46,
                'duration_minutes' => 20 * 60,
                'time_slots' => ['05:30', '09:00', '18:00'],
                'price_variance' => 6,
            ],
            [
                'operator' => 'Phuong Trang (Futa)',
                'from' => 'Bến xe Miền Tây',
                'to' => 'Bến xe Cần Thơ',
                'price' => 1150,
                'seats' => 40,
                'duration_minutes' => 210,
                'time_slots' => ['05:30', '08:30', '12:00', '15:30', '19:00'],
                'price_variance' => 3,
            ],
        ];

        $planeRoutes = [
            [
                'operator' => 'Vietnam Airlines',
                'from' => 'Sân bay Nội Bài (HAN)',
                'to' => 'Sân bay Tân Sơn Nhất (SGN)',
                'price' => 1900,
                'seats' => 186,
                'duration_minutes' => 135,
                'price_variance' => 8,
            ],
            [
                'operator' => 'Vietjet Air',
                'from' => 'Sân bay Tân Sơn Nhất (SGN)',
                'to' => 'Sân bay Nội Bài (HAN)',
                'price' => 1850,
                'seats' => 186,
                'duration_minutes' => 135,
                'price_variance' => 6,
            ],
            [
                'operator' => 'Bamboo Airways',
                'from' => 'Sân bay Nội Bài (HAN)',
                'to' => 'Sân bay Đà Nẵng (DAD)',
                'price' => 1750,
                'seats' => 168,
                'duration_minutes' => 85,
                'price_variance' => 5,
            ],
            [
                'operator' => 'Vietravel Airlines',
                'from' => 'Sân bay Đà Nẵng (DAD)',
                'to' => 'Sân bay Nội Bài (HAN)',
                'price' => 1720,
                'seats' => 156,
                'duration_minutes' => 90,
                'price_variance' => 4,
            ],
            [
                'operator' => 'Pacific Airlines',
                'from' => 'Sân bay Tân Sơn Nhất (SGN)',
                'to' => 'Sân bay Cam Ranh (CXR)',
                'price' => 1680,
                'seats' => 170,
                'duration_minutes' => 75,
                'price_variance' => 5,
            ],
            [
                'operator' => 'VASCO',
                'from' => 'Sân bay Cần Thơ (VCA)',
                'to' => 'Sân bay Tân Sơn Nhất (SGN)',
                'price' => 1650,
                'seats' => 120,
                'duration_minutes' => 55,
                'time_slots' => ['06:00', '09:15', '14:00', '18:30'],
                'price_variance' => 3,
            ],
        ];

        $trainRoutes = [
            [
                'operator' => 'Duong sat Viet Nam',
                'from' => 'Ga Hà Nội',
                'to' => 'Ga Đà Nẵng',
                'price' => 1300,
                'seats' => 96,
                'duration_minutes' => 13 * 60,
                'price_variance' => 3,
            ],
            [
                'operator' => 'SE1/SE2',
                'from' => 'Ga Đà Nẵng',
                'to' => 'Ga Sài Gòn',
                'price' => 1500,
                'seats' => 96,
                'duration_minutes' => 16 * 60,
                'price_variance' => 4,
            ],
            [
                'operator' => 'SE3/SE4',
                'from' => 'Ga Sài Gòn',
                'to' => 'Ga Hà Nội',
                'price' => 1520,
                'seats' => 96,
                'duration_minutes' => 17 * 60,
                'price_variance' => 4,
            ],
            [
                'operator' => 'Duong sat Viet Nam',
                'from' => 'Ga Hà Nội',
                'to' => 'Ga Vinh',
                'price' => 1100,
                'seats' => 120,
                'duration_minutes' => 6 * 60,
                'time_slots' => ['06:10', '13:40', '18:20'],
                'price_variance' => 2,
            ],
        ];

        $busDates = $this->dateRange('2025-11-01', 30);
        $planeDates = $this->dateRange('2025-11-01', 30);
        $trainDates = $this->dateRange('2025-11-01', 28);

        $nextId = 1;
        $payload = array_merge(
            $this->generateTrips($busRoutes, $busDates, ['05:45', '08:30', '12:30', '16:45', '21:10'], $operators, $stations, $nextId),
            $this->generateTrips($planeRoutes, $planeDates, ['06:00', '09:30', '14:15', '19:40'], $operators, $stations, $nextId),
            $this->generateTrips($trainRoutes, $trainDates, ['06:10', '13:40', '20:30'], $operators, $stations, $nextId)
        );

        DB::table('chuyen_dis')->insert($payload);
    }

    private function generateTrips(
        array $routes,
        array $dates,
        array $timeSlots,
        array $operators,
        array $stations,
        int &$nextId
    ): array {
        $records = [];
        $timestamp = now();

        foreach ($routes as $route) {
            $operatorId = $this->lookupId($operators, $route['operator']);
            $fromStation = $this->lookupStation($stations, $route['from']);
            $toStation = $this->lookupStation($stations, $route['to']);

            if (!$operatorId || !$fromStation || !$toStation) {
                $this->warnMissingReference(
                    $route,
                    $operatorId,
                    $fromStation['id'] ?? null,
                    $toStation['id'] ?? null
                );
                continue;
            }

            $routeDates = $route['dates'] ?? $dates;
            $routeTimes = $route['time_slots'] ?? $timeSlots;
            $durationMinutes = $route['duration_minutes'] ?? 360;
            $seats = $route['seats'] ?? 40;
            $basePrice = $this->resolveBasePrice($route['price'] ?? null);
            $demoOverride = $this->getDemoTripPrice();
            if ($demoOverride !== null) {
                $basePrice = $demoOverride;
            }
            $variance = $route['price_variance'] ?? 0;

            foreach ($routeDates as $date) {
                foreach ($routeTimes as $time) {
                    $departure = Carbon::parse("{$date} {$time}");
                    $arrival = (clone $departure)->addMinutes($durationMinutes);

                    $records[] = [
                        'id' => $nextId++,
                        'nha_van_hanh_id' => $operatorId,
                        'tram_di_id' => $fromStation['id'],
                        'tram_den_id' => $toStation['id'],
                        'noi_di_tinh_thanh_id' => $fromStation['tinh_thanh_id'],
                        'noi_den_tinh_thanh_id' => $toStation['tinh_thanh_id'],
                        'gio_khoi_hanh' => $departure->toDateTimeString(),
                        'gio_den' => $arrival->toDateTimeString(),
                        'gia_co_ban' => $this->priceWithVariance($basePrice, $departure, $variance),
                        'tong_ghe' => $seats,
                        'ghe_con' => $seats,
                        'trang_thai' => $route['status'] ?? 'CON_VE',
                        'ngay_tao' => $timestamp,
                        'ngay_cap_nhat' => $timestamp,
                    ];
                }
            }
        }

        return $records;
    }

    private function priceWithVariance(int $basePrice, Carbon $departure, int $varianceSteps): int
    {
        if ($varianceSteps <= 0) {
            return $basePrice;
        }

        $hash = crc32($departure->toDateTimeString());
        $offset = ($hash % (2 * $varianceSteps + 1)) - $varianceSteps;
        $price = $basePrice + ($offset * self::PRICE_VARIANCE_STEP);

        return max(self::MIN_TRIP_PRICE, min(self::MAX_TRIP_PRICE, $price));
    }

    private function dateRange(string $startDate, int $days): array
    {
        $dates = [];
        $start = Carbon::parse($startDate)->startOfDay();

        for ($i = 0; $i < $days; $i++) {
            $dates[] = $start->copy()->addDays($i)->toDateString();
        }

        return $dates;
    }

    private function buildLookupMap($items): array
    {
        $map = [];

        foreach ($items as $item) {
            if (!isset($item->ten, $item->id)) {
                continue;
            }

            $map[$this->normalizeKey($item->ten)] = $item->id;
        }

        return $map;
    }

    private function buildStationLookup($items): array
    {
        $map = [];

        foreach ($items as $item) {
            if (!isset($item->ten, $item->id)) {
                continue;
            }

            $map[$this->normalizeKey($item->ten)] = [
                'id' => $item->id,
                'tinh_thanh_id' => $item->tinh_thanh_id ?? null,
            ];
        }

        return $map;
    }

    private function lookupStation(array $map, string $name): ?array
    {
        $key = $this->normalizeKey($name);

        return $map[$key] ?? null;
    }

    private function lookupId(array $map, string $name): ?int
    {
        $key = $this->normalizeKey($name);

        return $map[$key] ?? null;
    }

    private function normalizeKey(string $value): string
    {
        return Str::of($value)
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/u', ' ')
            ->squish()
            ->value();
    }

    private function warnMissingReference(array $trip, ?int $operatorId, ?int $fromId, ?int $toId): void
    {
        if (!$this->command) {
            return;
        }

        $missing = [];
        if (!$operatorId) {
            $missing[] = "operator: {$trip['operator']}";
        }
        if (!$fromId) {
            $missing[] = "from: {$trip['from']}";
        }
        if (!$toId) {
            $missing[] = "to: {$trip['to']}";
        }

        $this->command->warn('Skipped trip because of missing references -> ' . implode(', ', $missing));
    }

    private function getDemoTripPrice(): ?int
    {
        if ($this->demoTripPriceResolved) {
            return $this->demoTripPriceOverride;
        }

        $value = env('DEMO_TRIP_PRICE');
        if ($value === null || $value === '') {
            $this->demoTripPriceResolved = true;
            $this->demoTripPriceOverride = null;
            return null;
        }

        if (!is_numeric($value)) {
            $this->demoTripPriceResolved = true;
            $this->demoTripPriceOverride = null;
            return null;
        }

        $this->demoTripPriceOverride = $this->resolveBasePrice((int) $value);
        $this->demoTripPriceResolved = true;

        return $this->demoTripPriceOverride;
    }

    private function resolveBasePrice($price): int
    {
        if (is_numeric($price)) {
            $priceValue = (int) $price;
            return min(self::MAX_TRIP_PRICE, max(self::MIN_TRIP_PRICE, $priceValue));
        }

        return random_int(self::MIN_TRIP_PRICE, self::MAX_TRIP_PRICE);
    }
}
