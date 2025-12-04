<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NguoiDung extends Authenticatable
{
    use HasApiTokens, Notifiable;

    // Tên bảng
    protected $table = 'nguoi_dungs';

    // Các trường fillable
    protected $fillable = [
        'ho_ten','email','so_dien_thoai','mat_khau','vai_tro','trang_thai',
    ];

    // Tên cột timestamp tùy chỉnh
    const CREATED_AT = 'ngay_tao';
    const UPDATED_AT = 'ngay_cap_nhat';

    // Ẩn các trường nhạy cảm khi trả về JSON
    protected $hidden = ['mat_khau','remember_token'];

    /**
     * Quan hệ 1-n với DonHang
     * Một người dùng có thể có nhiều đơn hàng
     */
    public function donHangs(): HasMany
    {
        return $this->hasMany(DonHang::class, 'nguoi_dung_id');
    }
}
