<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DonHang extends Model
{
    // Tên bảng
    protected $table = 'don_hangs';

    // Các trường fillable
    protected $fillable = [
        'nguoi_dung_id',
        'chuyen_di_id',
        'noi_di',
        'noi_den',
        'tram_don',
        'tram_tra',
        'so_hanh_khach',
        'ten_nha_van_hanh',
        'cong_thanh_toan',
        'ma_don',
        'ten_khach',
        'sdt_khach',
        'email_khach',
        'tong_tien',
        'trang_thai',
        'trang_thai_chuyen',
    ];

    // Tên cột timestamp tùy chỉnh
    const CREATED_AT = 'ngay_tao';
    const UPDATED_AT = 'ngay_cap_nhat';

    /**
     * Quan hệ n-1 với ChuyenDi
     */
    public function chuyenDi(): BelongsTo
    {
        return $this->belongsTo(ChuyenDi::class, 'chuyen_di_id');
    }

    /**
     * Quan hệ n-1 với NguoiDung
     */
    public function nguoiDung(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'nguoi_dung_id');
    }

    /**
     * Quan hệ 1-1 với Ticket
     */
    public function ticket(): HasOne
    {
        return $this->hasOne(Ticket::class, 'don_hang_id');
    }

    /**
     * Quan hệ 1-n với ThanhToan
     */
    public function thanhToans(): HasMany
    {
        return $this->hasMany(ThanhToan::class, 'don_hang_id')->orderByDesc('thoi_diem_thanh_toan');
    }

    /**
     * Quan hệ 1-n với ChiTietDonHang
     */
    public function chiTietDonHang(): HasMany
    {
        return $this->hasMany(ChiTietDonHang::class, 'don_hang_id');
    }
}
