<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhanHoi extends Model
{
    // Tên bảng
    protected $table = 'phan_hois';

    // Các trường fillable
    protected $fillable = [
        'nguoi_dung_id','don_hang_id','loai','tieu_de','noi_dung','trang_thai',
        'nguoi_phu_trach','tra_loi','ngay_tra_loi',
    ];

    // Tên cột timestamp tùy chỉnh
    const CREATED_AT = 'ngay_tao';
    const UPDATED_AT = 'ngay_cap_nhat';
}


