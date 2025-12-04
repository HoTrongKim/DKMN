<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhiDichVu extends Model
{
    // Tên bảng
    protected $table = 'phi_dich_vus';

    // Các trường fillable
    protected $fillable = ['ten','loai','gia_tri','loai_tinh','ap_dung_cho','trang_thai'];

    // Tên cột timestamp tùy chỉnh
    const CREATED_AT = 'ngay_tao';
    const UPDATED_AT = 'ngay_cap_nhat';
}


