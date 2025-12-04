<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\DonHang;

class ChuyenDi extends Model
{
    // Tên bảng
    protected $table = 'chuyen_dis';

    // Các trường có thể gán giá trị hàng loạt
    protected $fillable = [
        'nha_van_hanh_id',
        'tram_di_id',
        'tram_den_id',
        'noi_di_tinh_thanh_id',
        'noi_den_tinh_thanh_id',
        'gio_khoi_hanh',
        'gio_den',
        'gia_co_ban',
        'tong_ghe',
        'ghe_con',
        'trang_thai',
    ];

    // Tên cột timestamp tùy chỉnh
    const CREATED_AT = 'ngay_tao';
    const UPDATED_AT = 'ngay_cap_nhat';

    // Ép kiểu dữ liệu cho các cột
    protected $casts = [
        'gio_khoi_hanh' => 'datetime',
        'gio_den' => 'datetime',
    ];

    /**
     * Quan hệ n-1 với Tram (Trạm đi)
     */
    public function tramDi(): BelongsTo
    {
        return $this->belongsTo(Tram::class, 'tram_di_id');
    }

    /**
     * Quan hệ n-1 với Tram (Trạm đến)
     */
    public function tramDen(): BelongsTo
    {
        return $this->belongsTo(Tram::class, 'tram_den_id');
    }

    /**
     * Quan hệ n-1 với NhaVanHanh
     */
    public function nhaVanHanh(): BelongsTo
    {
        return $this->belongsTo(NhaVanHanh::class, 'nha_van_hanh_id');
    }

    /**
     * Quan hệ n-1 với TinhThanh (Nơi đi)
     */
    public function noiDiTinhThanh(): BelongsTo
    {
        return $this->belongsTo(TinhThanh::class, 'noi_di_tinh_thanh_id');
    }

    /**
     * Quan hệ n-1 với TinhThanh (Nơi đến)
     */
    public function noiDenTinhThanh(): BelongsTo
    {
        return $this->belongsTo(TinhThanh::class, 'noi_den_tinh_thanh_id');
    }

    /**
     * Quan hệ 1-n với Ghe
     * Một chuyến đi có nhiều ghế
     */
    public function ghes(): HasMany
    {
        return $this->hasMany(Ghe::class, 'chuyen_di_id');
    }

    /**
     * Quan hệ 1-n với DonHang
     * Một chuyến đi có thể có nhiều đơn hàng
     */
    public function donHangs(): HasMany
    {
        return $this->hasMany(DonHang::class, 'chuyen_di_id');
    }

}
