<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChiTietDonHang extends Model
{
    // Tên bảng trong cơ sở dữ liệu
    protected $table = 'chi_tiet_don_hangs';

    // Tắt tính năng tự động cập nhật timestamps (created_at, updated_at)
    public $timestamps = false;

    // Các trường có thể được gán giá trị hàng loạt
    protected $fillable = ['don_hang_id','ghe_id','ten_hanh_khach','sdt_hanh_khach','gia_ghe'];

    /**
     * Quan hệ n-1 với bảng DonHang
     * Một chi tiết đơn hàng thuộc về một đơn hàng
     */
    public function donHang(): BelongsTo
    {
        return $this->belongsTo(DonHang::class, 'don_hang_id');
    }

    /**
     * Quan hệ n-1 với bảng Ghe
     * Một chi tiết đơn hàng liên quan đến một ghế
     */
    public function ghe(): BelongsTo
    {
        return $this->belongsTo(Ghe::class, 'ghe_id');
    }
}

