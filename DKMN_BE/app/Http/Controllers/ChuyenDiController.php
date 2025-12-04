<?php

namespace App\Http\Controllers;

use App\Http\Resources\TripResource;
use App\Models\ChuyenDi;
use App\Models\TinhThanh;
use App\Models\Tram;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;

/**
 * Controller xử lý tìm kiếm và tra cứu chuyến đi cho khách hàng
 * Hỗ trợ search theo tỉnh/thành, bến, loại xe (bus/train/plane)
 * Tự động resolve provinces và stations, fallback thông minh
 */
class ChuyenDiController extends Controller
{
    private static ?bool $tripProvinceColumns = null;

    private const VEHICLE_TYPE_MAP = [
        'bus' => 'xe_khach',
        'train' => 'tau_hoa',
        'plane' => 'may_bay',
    ];

    private const STATION_FALLBACKS = [
        32 => ['Sân bay Tân Sơn Nhất (SGN)'],
        30 => ['Sân bay Long Thành (LTN)'],
    ];

    private const STATION_TYPE_MAP = [
        'bus' => 'ben_xe',
        'train' => 'ga_tau',
        'plane' => 'san_bay',
    ];

    /**
     * Lấy danh sách tất cả chuyến đi (dùng cho admin/internal)
     */
    public function getData()
    {
        // Trả về JSON response
        return response()->json(['data' => ChuyenDi::orderByDesc('ngay_tao')->get()]);
    }

    /**
     * Tìm kiếm chuyến đi theo điều kiện phức tạp
     * 
     * Filters:
     * - vehicleType: bus/train/plane (bắt buộc)
     * - from/fromId: Tỉnh đi (hỗ trợ ID hoặc tên)
     * - to/toId: Tỉnh đến
     * - departureDate: Ngày khởi hành
     * - returnDate: Ngày về (optional, cho round-trip)
     * - pickupStation/pickupStationId: Bến đón
     * - dropoffStation/dropoffStationId: Bến trả
     * - passengers: Số hành khách (để filter ghế còn)
     * - company/companyId: Nhà vận hành
     * 
     * Tự động resolve tỉnh/bến nếu chỉ có tên, hỗ trợ fallback thông minh
     */
    public function search(Request $request)
    {
        // Validate input request
        $validated = $request->validate([
            'vehicleType' => 'required|in:bus,train,plane',
            'from' => 'required_without:fromId|nullable|string|max:100',
            'fromId' => 'required_without:from|nullable|integer|exists:tinh_thanhs,id',
            'to' => 'required_without:toId|nullable|string|max:100|different:from',
            'toId' => 'required_without:to|nullable|integer|exists:tinh_thanhs,id',
            'departureDate' => 'required|date',
            'returnDate' => 'nullable|date|after_or_equal:departureDate',
            'pickupStation' => 'nullable|string|max:150',
            'pickupStationId' => 'nullable|integer|exists:trams,id',
            'dropoffStation' => 'nullable|string|max:150',
            'dropoffStationId' => 'nullable|integer|exists:trams,id',
            'passengers' => 'nullable|integer|min:1|max:50',
            'company' => 'nullable|string|max:150',
            'companyId' => 'nullable|integer|exists:nha_van_hanhs,id',
        ]);

        // Map loại phương tiện từ request sang DB value
        $vehicleTypeKey = $validated['vehicleType'];
        $vehicleTypeDb = self::VEHICLE_TYPE_MAP[$vehicleTypeKey] ?? null;
        $passengers = (int) ($validated['passengers'] ?? 1);

        // Tìm tỉnh đi và tỉnh đến (theo ID hoặc tên)
        $fromCity = $this->findTinhThanhById($validated['fromId'] ?? null)
            ?? $this->resolveTinhThanh($validated['from'] ?? '');
        $toCity = $this->findTinhThanhById($validated['toId'] ?? null)
            ?? $this->resolveTinhThanh($validated['to'] ?? '');

        // Nếu không tìm thấy tỉnh thành hợp lệ, trả về lỗi
        if (!$fromCity || !$toCity) {
            // Trả về JSON response
        return response()->json([
                'status' => false,
                'message' => 'Tinh thanh khong hop le',
            ], 422);
        }

        // Tính toán khoảng thời gian trong ngày (00:00:00 -> 23:59:59)
        [$departureStart, $departureEnd] = $this->dateBounds($validated['departureDate']);

        // Tìm trạm đón (pickup station)
        $pickupStation = $this->findTramById($validated['pickupStationId'] ?? null)
            ?? $this->resolveTram(
                $validated['pickupStation'] ?? null,
                $fromCity->id,
                $vehicleTypeKey
            );

        // Tìm trạm trả (dropoff station)
        $dropoffStation = $this->findTramById($validated['dropoffStationId'] ?? null)
            ?? $this->resolveTram(
                $validated['dropoffStation'] ?? null,
                $toCity->id,
                $vehicleTypeKey
            );

        // Xác định ID nhà vận hành và từ khóa tên nhà vận hành
        $companyId = $validated['companyId'] ?? null;
        $companyKeyword = $companyId ? null : $this->normalizeCompanyKeyword($validated['company'] ?? null);

        // Kiểm tra xem DB đã có cột tỉnh thành cho chuyến đi chưa
        $hasProvinceColumns = $this->hasTripProvinceColumns();

        // Xây dựng query cơ bản
        $baseQuery = ChuyenDi::query()
            ->with([
                'nhaVanHanh',
                'tramDi.tinhThanh',
                'tramDen.tinhThanh',
                'noiDiTinhThanh',
                'noiDenTinhThanh',
            ])
            // Lọc theo loại phương tiện (qua nhà vận hành)
            ->when($vehicleTypeDb, function ($query) use ($vehicleTypeDb) {
                $query->whereHas('nhaVanHanh', fn ($q) => $q->where('loai', $vehicleTypeDb));
            })
            // Lọc theo ID nhà vận hành cụ thể
            ->when($companyId, fn ($query) => $query->where('nha_van_hanh_id', $companyId))
            // Lọc theo khoảng thời gian khởi hành (trong ngày)
            ->whereBetween('gio_khoi_hanh', [$departureStart, $departureEnd])
            // Lọc theo tên nhà vận hành (nếu có)
            ->when($companyKeyword, function ($query) use ($companyKeyword) {
                $query->whereHas('nhaVanHanh', function ($q) use ($companyKeyword) {
                    $q->where('ten', 'like', "%{$companyKeyword}%");
                });
            })
            // Lọc theo số lượng hành khách (kiểm tra ghế còn trống)
            ->when($passengers, function ($query) use ($passengers) {
                $query->where(function ($sub) use ($passengers) {
                    // Trường hợp 1: Có cột ghe_con và đủ chỗ
                    $sub->where('ghe_con', '>=', $passengers)
                        // Trường hợp 2: ghe_con null (chưa cập nhật), check tong_ghe
                        ->orWhere(function ($fallback) use ($passengers) {
                            $fallback->whereNull('ghe_con')
                                ->where('tong_ghe', '>=', $passengers);
                        })
                        // Trường hợp 3: ghe_con <= 0 (lỗi data?), check tong_ghe
                        ->orWhere(function ($fallback) use ($passengers) {
                            $fallback->where('ghe_con', '<=', 0)
                                ->where('tong_ghe', '>=', $passengers);
                        });
                });
            })
            ->orderBy('gio_khoi_hanh');

        $buildLocationQuery = function (bool $useProvinceColumns) use ($baseQuery, $fromCity, $toCity) {
            $query = clone $baseQuery;

            if ($useProvinceColumns) {
                $this->applyProvinceAwareFilter($query, $fromCity->id, true);
                $this->applyProvinceAwareFilter($query, $toCity->id, false);
            } else {
                $query
                    ->whereHas('tramDi', fn ($q) => $this->applyStationFilter(
                        $q,
                        $fromCity->id,
                        $this->stationFallbacks($fromCity->id)
                    ))
                    ->whereHas('tramDen', fn ($q) => $this->applyStationFilter(
                        $q,
                        $toCity->id,
                        $this->stationFallbacks($toCity->id)
                    ));
            }

            return $query;
        };

        $executeQuery = function (bool $useProvinceColumns) use ($buildLocationQuery, $pickupStation, $dropoffStation) {
            $locationQuery = $buildLocationQuery($useProvinceColumns);
            $stationFilteredQuery = clone $locationQuery;

            if ($pickupStation?->id) {
                $stationFilteredQuery->where('tram_di_id', $pickupStation->id);
            }

            if ($dropoffStation?->id) {
                $stationFilteredQuery->where('tram_den_id', $dropoffStation->id);
            }

            $trips = $stationFilteredQuery->get();
            $stationFiltersApplied = (bool) ($pickupStation?->id || $dropoffStation?->id);
            $stationFiltersFallbackUsed = false;

            if ($stationFiltersApplied && $trips->isEmpty()) {
                $stationFiltersFallbackUsed = true;
                $trips = (clone $locationQuery)->get();
            }

            return [$trips, $stationFiltersFallbackUsed];
        };

        try {
            [$trips, $stationFiltersFallbackUsed] = $executeQuery($hasProvinceColumns);
        } catch (QueryException $exception) {
            $message = $exception->getMessage();
            $missingProvinceColumns =
                str_contains($message, 'noi_di_tinh_thanh_id') ||
                str_contains($message, 'noi_den_tinh_thanh_id');

            if ($hasProvinceColumns && $missingProvinceColumns) {
                // Fallback gracefully when DB schema has not added province columns yet.
                self::$tripProvinceColumns = false;
                [$trips, $stationFiltersFallbackUsed] = $executeQuery(false);
            } else {
                throw $exception;
            }
        }

        return TripResource::collection($trips)->additional([
            'status' => true,
            'message' => $stationFiltersFallbackUsed
                ? 'Khong co chuyen phu hop voi ben da chon, hien thi cac chuyen cung tinh.'
                : 'Tim thay danh sach chuyen di',
            'meta' => [
                'count' => $trips->count(),
                'vehicleType' => $vehicleTypeKey,
                'from' => $fromCity->ten,
                'to' => $toCity->ten,
                'departureDate' => $validated['departureDate'],
                'passengers' => $passengers,
                'pickupStation' => $pickupStation?->ten,
                'dropoffStation' => $dropoffStation?->ten,
                'stationFiltersFallback' => $stationFiltersFallbackUsed,
            ],
        ]);
    }

    private function applyProvinceAwareFilter($query, int $cityId, bool $isDeparture): void
    {
        $column = $isDeparture ? 'noi_di_tinh_thanh_id' : 'noi_den_tinh_thanh_id';
        $relation = $isDeparture ? 'tramDi' : 'tramDen';
        $fallbacks = $this->stationFallbacks($cityId);

        $query->where(function ($builder) use ($column, $relation, $cityId, $fallbacks) {
            $builder->where($column, $cityId)
                ->orWhere(function ($sub) use ($column, $relation, $cityId, $fallbacks) {
                    $sub->whereNull($column)
                        ->whereHas($relation, fn ($q) => $this->applyStationFilter($q, $cityId, $fallbacks));
                });
        });
    }

    private function hasTripProvinceColumns(): bool
    {
        if (self::$tripProvinceColumns === null) {
            self::$tripProvinceColumns =
                Schema::hasColumn('chuyen_dis', 'noi_di_tinh_thanh_id') &&
                Schema::hasColumn('chuyen_dis', 'noi_den_tinh_thanh_id');
        }

        return self::$tripProvinceColumns;
    }

    private function findTinhThanhById(?int $id): ?TinhThanh
    {
        if (!$id) {
            return null;
        }

        return TinhThanh::find($id);
    }

    private function resolveTinhThanh(string $input): ?TinhThanh
    {
        $trimmed = trim($input);

        if ($trimmed === '') {
            return null;
        }

        if (is_numeric($trimmed)) {
            return TinhThanh::find((int) $trimmed);
        }

        $normalized = Str::lower($trimmed);
        $asciiNormalized = Str::lower(Str::ascii($trimmed));
        $code = Str::upper(Str::slug($asciiNormalized, ''));

        $directMatch = TinhThanh::query()
            ->whereRaw('LOWER(ten) = ?', [$normalized])
            ->orWhereRaw('LOWER(ten) = ?', [$asciiNormalized])
            ->orWhere('ma', $code)
            ->first();

        if ($directMatch) {
            return $directMatch;
        }

        return TinhThanh::all()->first(function ($item) use ($asciiNormalized) {
            return Str::lower(Str::ascii($item->ten)) === $asciiNormalized;
        });
    }

    private function findTramById(?int $id): ?Tram
    {
        if (!$id) {
            return null;
        }

        return Tram::find($id);
    }

    private function resolveTram(?string $value, ?int $tinhThanhId, string $vehicleTypeKey): ?Tram
    {
        if (!$value) {
            return null;
        }

        $keyword = trim($value);
        if ($keyword === '') {
            return null;
        }

        $baseQuery = Tram::query();

        if ($tinhThanhId) {
            $baseQuery->where('tinh_thanh_id', $tinhThanhId);
        }

        $stationType = self::STATION_TYPE_MAP[$vehicleTypeKey] ?? null;
        if ($stationType) {
            $baseQuery->where('loai', $stationType);
        }

        $normalized = Str::lower(Str::ascii($keyword));

        $station = (clone $baseQuery)
            ->where(function ($query) use ($keyword, $normalized) {
                $query->where('ten', 'like', '%' . $keyword . '%')
                    ->orWhere('ten', 'like', '%' . Str::ascii($keyword) . '%')
                    ->orWhereRaw('LOWER(ten) = ?', [$normalized]);
            })
            ->first();

        if ($station) {
            return $station;
        }

        return $baseQuery->get()->first(function ($tram) use ($normalized) {
            $name = Str::lower(Str::ascii($tram->ten));
            return Str::contains($name, $normalized);
        });
    }

    private function normalizeCompanyKeyword(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        $cleaned = preg_replace('/^(Xe khach|Tau hoa|May bay)\s+/iu', '', $value);

        return trim($cleaned);
    }

    private function dateBounds(string $date): array
    {
        $start = Carbon::parse($date)->startOfDay();

        return [$start, $start->copy()->endOfDay()];
    }

    private function stationFallbacks(int $cityId): array
    {
        return self::STATION_FALLBACKS[$cityId] ?? [];
    }

    private function applyStationFilter($query, int $cityId, array $fallbacks): void
    {
        $query->where(function ($sub) use ($cityId, $fallbacks) {
            $sub->where('tinh_thanh_id', $cityId);

            if ($fallbacks) {
                $sub->orWhereIn('ten', $fallbacks);
            }
        });
    }
}
