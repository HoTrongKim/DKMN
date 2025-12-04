<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ThongKeDoanhThu extends Model
{
    // Tên bảng
    protected $table = 'thong_ke_doanh_thus';
    
    // Tắt timestamps
    public $timestamps = false;
    
    // Các trường fillable
    protected $fillable = [
        'ngay','loai_phuong_tien','so_don_hang','tong_doanh_thu','doanh_thu_thuc',
        'so_ve_ban','so_ve_huy','ty_le_huy',
    ];
}


