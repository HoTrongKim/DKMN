<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DanhGia extends Model
{
    // Tên bảng
    protected $table = 'danh_gias';
    
    // Tắt timestamps mặc định
    public $timestamps = false;

    // Các trường fillable
    protected $fillable = [
        'nguoi_dung_id',
        'chuyen_di_id',
        'don_hang_id',
        'diem',
        'nhan_xet',
        'trang_thai',
        'ngay_tao',
    ];

    // Cột created_at tùy chỉnh
    const CREATED_AT = 'ngay_tao';

    /**
     * Quan hệ n-1 với NguoiDung
     */
    public function nguoiDung(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'nguoi_dung_id');
    }

    /**
     * Quan hệ n-1 với ChuyenDi
     */
    public function chuyenDi(): BelongsTo
    {
        return $this->belongsTo(ChuyenDi::class, 'chuyen_di_id');
    }

    /**
     * Quan hệ n-1 với DonHang
     */
    public function donHang(): BelongsTo
    {
        return $this->belongsTo(DonHang::class, 'don_hang_id');
    }
}

