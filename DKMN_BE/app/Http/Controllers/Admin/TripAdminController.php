<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\TripCustomerNotificationMail;
use App\Models\ChuyenDi;
use App\Models\NguoiDung;
use App\Models\ThongBao;
use App\Models\Tram;
use App\Services\TripSeatSynchronizer;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Controller quản lý chuyến đi/tuyến (trips/routes) cho Admin
 * CRUD trips, quản lý ghế, validation tuyến (province consistency)
 * Gửi thông báo email + app khi có thay đổi chuyến đi
 */
class TripAdminController extends Controller
{
    private static ?bool $tripProvinceColumns = null;

    /**
     * Danh sách chuyến đi có filter phức tạp: keyword, status, type, operatorId, dateFrom, dateTo
     * Eager load: nhà vận hành, trạm đi/đến, tỉnh thành
     * Logic:
     * - Tìm kiếm theo ID chuyến hoặc tên nhà xe/trạm
     * - Lọc theo trạng thái (normalized), loại phương tiện, nhà xe
     * - Lọc theo khoảng thời gian khởi hành
     * - Sắp xếp giảm dần theo giờ khởi hành
     */
    /**
     * Danh sách chuyến đi có filter phức tạp: keyword, status, type, operatorId, dateFrom, dateTo
     * Eager load: nhà vận hành, trạm đi/đến, tỉnh thành
     * Logic:
     * - Tìm kiếm theo ID chuyến hoặc tên nhà xe/trạm
     * - Lọc theo trạng thái (normalized), loại phương tiện, nhà xe
     * - Lọc theo khoảng thời gian khởi hành
     * - Sắp xếp giảm dần theo giờ khởi hành
     */
        /**
     * Lấy danh sách dữ liệu với phân trang và filter
     */
    public function index(Request $request)
    {
        // Validate các tham số lọc đầu vào
        $validated = $request->validate([
            'keyword' => 'nullable|string|max:150', // Từ khóa tìm kiếm
            'status' => 'nullable|string|in:AVAILABLE,SOLD_OUT,CANCELLED,CON_VE,HET_VE,HUY,con_ve,het_ve,huy', // Trạng thái (hỗ trợ cả tiếng Anh/Việt)
            'type' => 'nullable|string|in:bus,train,plane', // Loại phương tiện
            'operatorId' => 'nullable|integer|exists:nha_van_hanhs,id', // Lọc theo nhà xe
            'dateFrom' => 'nullable|date', // Ngày khởi hành từ
            'dateTo' => 'nullable|date|after_or_equal:dateFrom', // Ngày khởi hành đến
        ]);

        // Eager load các quan hệ để hiển thị thông tin đầy đủ và tối ưu query
        // Load cả tỉnh thành của trạm đi/đến và tỉnh thành trực tiếp của chuyến (nếu có)
        $query = ChuyenDi::query()
            ->with([
                'nhaVanHanh',
                'tramDi.tinhThanh',
                'tramDen.tinhThanh',
                'noiDiTinhThanh',
                'noiDenTinhThanh',
            ])
            ->orderByDesc('gio_khoi_hanh'); // Sắp xếp chuyến mới nhất lên đầu

        // Xử lý tìm kiếm từ khóa
        if (!empty($validated['keyword'])) {
            $keyword = Str::lower(trim($validated['keyword']));
            $query->where(function ($sub) use ($keyword) {
                // Tìm chính xác theo ID chuyến đi
                $sub->where('id', (int) $keyword)
                    // Hoặc tìm theo tên nhà xe
                    ->orWhereHas('nhaVanHanh', fn ($q) => $q->where('ten', 'like', "%{$keyword}%"))
                    // Hoặc tìm theo tên trạm đi
                    ->orWhereHas('tramDi', fn ($q) => $q->where('ten', 'like', "%{$keyword}%"))
                    // Hoặc tìm theo tên trạm đến
                    ->orWhereHas('tramDen', fn ($q) => $q->where('ten', 'like', "%{$keyword}%"));
            });
        }

        // Xử lý lọc theo trạng thái
        if (!empty($validated['status'])) {
            // Chuẩn hóa status về dạng DB (CON_VE, HET_VE, HUY)
            $status = $this->normalizeStatus($validated['status']);
            if ($status) {
                $query->where('trang_thai', $status);
            }
        }

        // Xử lý lọc theo loại phương tiện
        if (!empty($validated['type'])) {
            // Map từ frontend type (bus/train) sang DB type (xe_khach/tau_hoa)
            $type = $this->mapFrontendTypeToInternal($validated['type']);
            if ($type) {
                $query->whereHas('nhaVanHanh', fn ($q) => $q->where('loai', $type));
            }
        }

        // Xử lý lọc theo nhà xe cụ thể
        if (!empty($validated['operatorId'])) {
            $query->where('nha_van_hanh_id', $validated['operatorId']);
        }

        // Xử lý lọc theo khoảng thời gian khởi hành
        if (!empty($validated['dateFrom'])) {
            $query->where('gio_khoi_hanh', '>=', Carbon::parse($validated['dateFrom'])->startOfDay());
        }

        if (!empty($validated['dateTo'])) {
            $query->where('gio_khoi_hanh', '<=', Carbon::parse($validated['dateTo'])->endOfDay());
        }

        // Phân trang và transform dữ liệu
        $paginator = $query->paginate($this->resolvePerPage($request));
        $data = $paginator->getCollection()
            ->map(fn (ChuyenDi $trip) => $this->transformTrip($trip));

        return $this->respondWithPagination($paginator, $data);
    }

    /**
     * Chi tiết chuyến đi với đầy đủ thông tin: trip, operator, stations, provinces
     */
        /**
     * Lấy chi tiết một bản ghi theo ID
     */
    public function show(ChuyenDi $chuyenDi)
    {
        // Load các quan hệ cần thiết cho chi tiết chuyến đi
        $chuyenDi->load([
            'nhaVanHanh',
            'tramDi.tinhThanh',
            'tramDen.tinhThanh',
            'noiDiTinhThanh',
            'noiDenTinhThanh',
        ]);

        // Trả về JSON response
        return response()->json([
            'status' => true,
            'data' => $this->transformTrip($chuyenDi),
        ]);
    }

    /**
     * Tạo chuyến đi mới
     * Validate: province consistency (trạm đi/đến phải khác tỉnh)
     * Auto sync seats sau khi tạo
     */
        /**
     * Tạo mới bản ghi
     */
    public function store(Request $request)
    {
        // Validate và chuẩn bị dữ liệu payload
        $payload = $this->validateTripPayload($request);

        // Tạo chuyến đi mới
        $trip = ChuyenDi::create($payload);
        
        // Đồng bộ ghế cho chuyến đi (tạo ghế trống dựa trên tổng số ghế)
        TripSeatSynchronizer::sync($trip);
        
        // Reload lại model với các quan hệ để trả về response đầy đủ
        $trip = $trip->fresh([
            'nhaVanHanh',
            'tramDi.tinhThanh',
            'tramDen.tinhThanh',
            'noiDiTinhThanh',
            'noiDenTinhThanh',
        ]);

        // Trả về JSON response
        return response()->json([
            'status' => true,
            'message' => 'Tạo chuyến đi thành công.',
            'data' => $this->transformTrip($trip),
        ], 201);
    }

    /**
     * Cập nhật chuyến đi
     * Nếu sửa giá/thời gian/tuyến → nên thông báo khách đã đặt vé
     * Auto sync seats sau khi update
     */
        /**
     * Cập nhật bản ghi theo ID
     */
    public function update(Request $request, ChuyenDi $chuyenDi)
    {
        // Validate dữ liệu cập nhật (cho phép partial update)
        $payload = $this->validateTripPayload($request, true);

        if (!empty($payload)) {
            $payload['ngay_cap_nhat'] = now();
            $chuyenDi->fill($payload)->save();
        }

        // Đồng bộ lại ghế nếu tổng số ghế thay đổi
        TripSeatSynchronizer::sync($chuyenDi);

        $chuyenDi->load([
            'nhaVanHanh',
            'tramDi.tinhThanh',
            'tramDen.tinhThanh',
            'noiDiTinhThanh',
            'noiDenTinhThanh',
        ]);

        // Trả về JSON response
        return response()->json([
            'status' => true,
            'message' => 'Cập nhật chuyến đi thành công.',
            'data' => $this->transformTrip($chuyenDi),
        ]);
    }

    /**
     * Xóa chuyến đi (chỉ cho phép nếu chưa có đơn hàng)
     */
        /**
     * Xóa bản ghi theo ID
     */
    public function destroy(ChuyenDi $chuyenDi)
    {
        // Kiểm tra ràng buộc toàn vẹn dữ liệu
        if ($chuyenDi->donHangs()->exists()) {
            // Trả về JSON response
        return response()->json([
                'status' => false,
                'message' => 'Không thể xóa chuyến đi vì đã phát sinh đơn hàng.',
            ], 422);
        }

        $chuyenDi->delete();

        // Trả về JSON response
        return response()->json([
            'status' => true,
            'message' => 'Đã xóa chuyến đi.',
        ]);
    }

    /**
     * Gửi thông báo tới khách đã đặt vé về thay đổi chuyến đi
     * Hỗ trợ channels: email, app (in-app notification)
     */
    public function notify(Request $request, ChuyenDi $chuyenDi)
    {
        // Validate thông tin gửi thông báo
        $validated = $request->validate([
            'message' => 'required|string|max:1000', // Nội dung thông báo
            'channels' => ['required', 'array', 'min:1'], // Kênh gửi: email, app
            'channels.*' => ['required', Rule::in(['email', 'app', 'sms'])],
            'recipientIds' => ['required', 'array', 'min:1'], // Danh sách ID người nhận
            'recipientIds.*' => ['integer', 'exists:nguoi_dungs,id'],
        ]);

        $channels = collect($validated['channels'])->unique()->values()->all();
        // Lấy danh sách người dùng cần gửi (loại trừ admin)
        $recipients = NguoiDung::query()
            ->whereIn('id', $validated['recipientIds'])
            ->where('vai_tro', '!=', 'quan_tri')
            ->get();

        if ($recipients->isEmpty()) {
            // Trả về JSON response
        return response()->json([
                'status' => false,
                'message' => 'Khong tim thay khach hang hop le.',
            ], 422);
        }

        $chuyenDi->loadMissing(['nhaVanHanh', 'tramDi.tinhThanh', 'tramDen.tinhThanh']);
        $title = $this->buildNotificationTitle($chuyenDi);
        $summary = $this->buildTripSummary($chuyenDi);

        $appCount = 0;
        $emailCount = 0;

        // Duyệt qua từng người nhận và gửi thông báo theo các kênh đã chọn
        foreach ($recipients as $recipient) {
            // Gửi thông báo in-app
            if (in_array('app', $channels, true)) {
                ThongBao::create([
                    'nguoi_dung_id' => $recipient->id,
                    'tieu_de' => $title,
                    'noi_dung' => $validated['message'],
                    'loai' => 'trip_update',
                    'da_doc' => 0,
                ]);
                $appCount++;
            }

            // Gửi email thông báo
            if (in_array('email', $channels, true) && !empty($recipient->email)) {
                Mail::to($recipient->email)->send(
                    new TripCustomerNotificationMail($recipient, $chuyenDi, $validated['message'], $summary)
                );
                $emailCount++;
            }
        }

        // Trả về JSON response
        return response()->json([
            'status' => true,
            'message' => 'Da gui thong bao den khach hang.',
            'data' => [
                'recipients' => $recipients->count(),
                'app' => $appCount,
                'email' => $emailCount,
                'sms' => in_array('sms', $channels, true) ? 0 : null,
            ],
        ]);
    }

    /**
     * Build notification title: "Thông báo chuyến X → Y (HH:mm dd/mm/YYYY)"
     */
    private function buildNotificationTitle(ChuyenDi $trip): string
    {
        $from = $trip->tramDi->ten ?? 'Điểm đi';
        $to = $trip->tramDen->ten ?? 'Điểm đến';
        $time = optional($trip->gio_khoi_hanh)->timezone('Asia/Ho_Chi_Minh')->format('H:i d/m/Y');

        return trim(sprintf('Thông báo chuyến %s → %s %s', $from, $to, $time ? "($time)" : ''));
    }

    /**
     * Build trip summary array cho email/notification
     * Trả về: route, operator, vehicle, departure, arrival
     */
    private function buildTripSummary(ChuyenDi $trip): array
    {
        return [
            'route' => trim(($trip->tramDi->ten ?? '...') . ' → ' . ($trip->tramDen->ten ?? '...')),
            'operator' => $trip->nhaVanHanh->ten ?? null,
            'vehicle' => $trip->nhaVanHanh->loai ?? null,
            'departure' => optional($trip->gio_khoi_hanh)->timezone('Asia/Ho_Chi_Minh')->format('H:i d/m/Y'),
            'arrival' => optional($trip->gio_den)->timezone('Asia/Ho_Chi_Minh')->format('H:i d/m/Y'),
        ];
    }

    /**
     * Convert string có dấu sang ASCII (dùng cho keyword search)
     */
    private function ascii(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return Str::ascii($value);
    }

    /**
     * Validate trip payload (tạo mới hoặc update)
     * Check: province consistency (trạm đi/đến phải khác tỉnh)
     * Auto fill province IDs từ station nếu chưa có
     */
    private function validateTripPayload(Request $request, bool $isUpdate = false): array
    {
        $hasProvinceColumns = $this->hasTripProvinceColumns();
        // Rule validate cho province ID (chỉ bắt buộc nếu DB có cột này)
        $provinceRule = $hasProvinceColumns
            ? [$isUpdate ? 'sometimes' : 'required', 'integer', 'exists:tinh_thanhs,id']
            : ['nullable', 'integer', 'exists:tinh_thanhs,id'];

        $rules = [
            'operatorId' => [$isUpdate ? 'sometimes' : 'required', 'integer', 'exists:nha_van_hanhs,id'],
            'fromProvinceId' => $provinceRule,
            'toProvinceId' => $provinceRule,
            'fromStationId' => [$isUpdate ? 'sometimes' : 'required', 'integer', 'exists:trams,id', 'different:toStationId'], // Trạm đi/đến phải khác nhau
            'toStationId' => [$isUpdate ? 'sometimes' : 'required', 'integer', 'exists:trams,id'],
            'departureTime' => [$isUpdate ? 'sometimes' : 'required', 'date'],
            'arrivalTime' => [$isUpdate ? 'sometimes' : 'required', 'date', 'after:departureTime'], // Giờ đến phải sau giờ đi
            'basePrice' => [$isUpdate ? 'sometimes' : 'required', 'numeric', 'min:0'],
            'totalSeats' => [$isUpdate ? 'sometimes' : 'required', 'integer', 'min:1', 'max:200'],
            'remainingSeats' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', 'string', Rule::in(['AVAILABLE', 'SOLD_OUT', 'CANCELLED', 'CON_VE', 'HET_VE', 'HUY'])],
        ];

        $validated = $request->validate($rules);
        $payload = [];
        $fromStationModel = null;
        $toStationModel = null;

        // Map dữ liệu từ request sang DB columns
        if (array_key_exists('operatorId', $validated)) {
            $payload['nha_van_hanh_id'] = $validated['operatorId'];
        }
        if ($hasProvinceColumns && array_key_exists('fromProvinceId', $validated)) {
            $payload['noi_di_tinh_thanh_id'] = $validated['fromProvinceId'];
        }
        if ($hasProvinceColumns && array_key_exists('toProvinceId', $validated)) {
            $payload['noi_den_tinh_thanh_id'] = $validated['toProvinceId'];
        }
        
        // Xử lý trạm đi
        if (array_key_exists('fromStationId', $validated)) {
            $payload['tram_di_id'] = $validated['fromStationId'];
            // Thao tác database
        $fromStationModel = Tram::find($validated['fromStationId']);
            if (!$fromStationModel) {
                throw ValidationException::withMessages([
                    'fromStationId' => 'Bến xuất phát không hợp lệ.',
                ]);
            }
        }
        
        // Xử lý trạm đến
        if (array_key_exists('toStationId', $validated)) {
            $payload['tram_den_id'] = $validated['toStationId'];
            // Thao tác database
        $toStationModel = Tram::find($validated['toStationId']);
            if (!$toStationModel) {
                throw ValidationException::withMessages([
                    'toStationId' => 'Bến đến không hợp lệ.',
                ]);
            }
        }
        
        // Tự động điền province ID từ trạm nếu chưa có và validate consistency
        if ($hasProvinceColumns) {
            if (!array_key_exists('noi_di_tinh_thanh_id', $payload) && $fromStationModel) {
                $payload['noi_di_tinh_thanh_id'] = $fromStationModel->tinh_thanh_id;
            }
            if (!array_key_exists('noi_den_tinh_thanh_id', $payload) && $toStationModel) {
                $payload['noi_den_tinh_thanh_id'] = $toStationModel->tinh_thanh_id;
            }
            // Đảm bảo trạm thuộc đúng tỉnh đã chọn
            $this->validateStationProvinceConsistency($fromStationModel, $payload['noi_di_tinh_thanh_id'] ?? null, 'fromProvinceId');
            $this->validateStationProvinceConsistency($toStationModel, $payload['noi_den_tinh_thanh_id'] ?? null, 'toProvinceId');
        }

        if (array_key_exists('departureTime', $validated)) {
            $payload['gio_khoi_hanh'] = Carbon::parse($validated['departureTime']);
        }
        if (array_key_exists('arrivalTime', $validated)) {
            $payload['gio_den'] = Carbon::parse($validated['arrivalTime']);
        }
        if (array_key_exists('basePrice', $validated)) {
            $payload['gia_co_ban'] = $validated['basePrice'];
        }
        if (array_key_exists('totalSeats', $validated)) {
            $payload['tong_ghe'] = $validated['totalSeats'];
            // Nếu tạo mới và không set remainingSeats thì mặc định bằng totalSeats
            if (!isset($validated['remainingSeats']) && !$isUpdate) {
                $payload['ghe_con'] = $validated['totalSeats'];
            }
        }
        if (array_key_exists('remainingSeats', $validated)) {
            $remaining = $validated['remainingSeats'];
            // Đảm bảo số ghế còn lại không vượt quá tổng số ghế
            if (isset($payload['tong_ghe'])) {
                $remaining = min($payload['tong_ghe'], $remaining);
            }
            $payload['ghe_con'] = $remaining;
        }
        if (array_key_exists('status', $validated)) {
            $normalized = $this->normalizeStatus($validated['status']);
            if ($normalized) {
                $payload['trang_thai'] = $normalized;
            }
        }

        return $payload;
    }

    /**
     * Validate station và province consistency: station phải thuộc province đã chọn
     */
    private function validateStationProvinceConsistency(?Tram $station, ?int $provinceId, string $field): void
    {
        if (!$station || !$provinceId) {
            return;
        }

        if ((int) $station->tinh_thanh_id !== (int) $provinceId) {
            throw ValidationException::withMessages([
                $field => 'Bến không thuộc tỉnh/thành đã chọn.',
            ]);
        }
    }

    /**
     * Check xem bảng chuyen_dis có columns noi_di_tinh_thanh_id và noi_den_tinh_thanh_id không
     * Cache kết quả trong static variable
     */
    private function hasTripProvinceColumns(): bool
    {
        if (self::$tripProvinceColumns === null) {
            self::$tripProvinceColumns =
                Schema::hasColumn('chuyen_dis', 'noi_di_tinh_thanh_id') &&
                Schema::hasColumn('chuyen_dis', 'noi_den_tinh_thanh_id');
        }

        return self::$tripProvinceColumns;
    }

    /**
     * Transform trip object sang format API response
     * Tính toán derived status (COMPLETED nếu đã qua giờ đến, map status labels)
     */
    private function transformTrip(ChuyenDi $trip): array
    {
        $operator = $trip->nhaVanHanh;
        $from = $trip->tramDi;
        $to = $trip->tramDen;
        $departureTime = $trip->gio_khoi_hanh?->toIso8601String();
        $arrivalTime = $trip->gio_den?->toIso8601String();
        $fromProvinceId = $trip->noi_di_tinh_thanh_id ?? $from?->tinh_thanh_id;
        $toProvinceId = $trip->noi_den_tinh_thanh_id ?? $to?->tinh_thanh_id;
        $fromProvinceName = $trip->noiDiTinhThanh->ten ?? $from?->tinhThanh?->ten;
        $toProvinceName = $trip->noiDenTinhThanh->ten ?? $to?->tinhThanh?->ten;

        // Tính toán trạng thái hiển thị (ví dụ: đã hoàn thành nếu quá giờ)
        $derivedStatus = $this->deriveStatus($trip);

        return [
            'id' => $trip->id,
            'type' => $this->mapOperatorType($operator?->loai),
            'operatorType' => $operator?->loai,
            'carrier' => $operator?->ten,
            'carrierAscii' => $this->ascii($operator?->ten),
            'operatorId' => $operator?->id,
            'operator' => $operator?->ten,
            'route' => trim(($from?->ten ?? '') . ' - ' . ($to?->ten ?? '')),
            'routeAscii' => $this->ascii(trim(($from?->ten ?? '') . ' - ' . ($to?->ten ?? ''))),
            'departureLocation' => $from?->ten ?? '',
            'departureLocationAscii' => $this->ascii($from?->ten ?? ''),
            'arrivalLocation' => $to?->ten ?? '',
            'arrivalLocationAscii' => $this->ascii($to?->ten ?? ''),
            'fromStationId' => $from?->id,
            'fromStation' => $from?->ten,
            'toStationId' => $to?->id,
            'toStation' => $to?->ten,
            'fromProvinceId' => $fromProvinceId,
            'fromProvinceName' => $fromProvinceName,
            'toProvinceId' => $toProvinceId,
            'toProvinceName' => $toProvinceName,
            'departureTime' => $departureTime,
            'arrivalTime' => $arrivalTime,
            'departAt' => $departureTime,
            'arrivalAt' => $arrivalTime,
            'basePrice' => (float) $trip->gia_co_ban,
            'seats' => [
                'total' => (int) $trip->tong_ghe,
                'remaining' => (int) $trip->ghe_con,
            ],
            'totalSeats' => (int) $trip->tong_ghe,
            'availableSeats' => (int) $trip->ghe_con,
            'status' => $derivedStatus['code'],
            'statusLabel' => $derivedStatus['label'],
            'rawStatus' => $trip->trang_thai,
            'createdAt' => $trip->ngay_tao?->toIso8601String(),
            'updatedAt' => $trip->ngay_cap_nhat?->toIso8601String(),
        ];
    }

    /**
     * Derive status từ trip: check HUY, check đã qua giờ đến (COMPLETED), hoặc map từ trang_thai
     */
    private function deriveStatus(ChuyenDi $trip): array
    {
        $rawNormalized = $this->normalizeStatus($trip->trang_thai);

        if ($rawNormalized === 'HUY') {
            return [
                'code' => 'CANCELLED',
                'label' => 'Đã hủy',
            ];
        }

        $arrival = $trip->gio_den;
        // Nếu đã quá giờ đến thì coi như đã hoàn thành
        if ($arrival && $arrival->isPast()) {
            return [
                'code' => 'COMPLETED',
                'label' => 'Đã hoàn thành',
            ];
        }

        $code = $this->mapTripStatusCode($trip->trang_thai);

        return [
            'code' => $code,
            'label' => $this->mapTripStatus($trip->trang_thai),
        ];
    }

    /**
     * Map trip status sang status code (AVAILABLE/SOLD_OUT/CANCELLED/COMPLETED)
     */
    private function mapTripStatusCode(?string $status): string
    {
        return match ($status) {
            'CON_VE' => 'AVAILABLE',
            'HET_VE' => 'SOLD_OUT',
            'HUY' => 'CANCELLED',
            'COMPLETED' => 'COMPLETED',
            default => 'UNKNOWN',
        };
    }

    /**
     * Map trip status sang label tiếng Việt
     */
    private function mapTripStatus(?string $status): string
    {
        return match ($status) {
            'CON_VE' => 'Còn vé',
            'HET_VE' => 'Hết vé',
            'HUY' => 'Đã hủy',
            'COMPLETED' => 'Đã hoàn thành',
            default => 'Không xác định',
        };
    }

    /**
     * Normalize status: AVAILABLE/CON_VE → CON_VE, SOLD_OUT/HET_VE → HET_VE, CANCELLED/HUY → HUY
     */
    private function normalizeStatus(?string $status): ?string
    {
        if (!$status) {
            return null;
        }

        return match (strtoupper($status)) {
            'AVAILABLE', 'CON_VE' => 'CON_VE',
            'SOLD_OUT', 'HET_VE' => 'HET_VE',
            'CANCELLED', 'HUY' => 'HUY',
            default => null,
        };
    }

    /**
     * Map operator type từ DB (tau_hoa/may_bay/xe_khach) sang API format (train/plane/bus)
     */
    private function mapOperatorType(?string $type): string
    {
        return match ($type) {
            'tau_hoa' => 'train',
            'may_bay' => 'plane',
            'xe_khach' => 'bus',
            default => 'bus',
        };
    }

    /**
     * Map frontend type (bus/train/plane) sang internal DB value (xe_khach/tau_hoa/may_bay)
     */
    private function mapFrontendTypeToInternal(?string $type): ?string
    {
        if (!$type) {
            return null;
        }

        return match (strtolower($type)) {
            'bus' => 'xe_khach',
            'train' => 'tau_hoa',
            'plane' => 'may_bay',
            default => null,
        };
    }
}
