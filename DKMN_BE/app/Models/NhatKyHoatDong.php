<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NhatKyHoatDong extends Model
{
    // Tên bảng
    protected $table = 'nhat_ky_hoat_dongs';
    
    // Tắt timestamps
    public $timestamps = false;
    
    // Các trường fillable
    protected $fillable = ['nguoi_dung_id','hanh_dong','mo_ta','ip','user_agent'];
}


