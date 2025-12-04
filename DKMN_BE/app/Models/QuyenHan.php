<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuyenHan extends Model
{
    // Tên bảng
    protected $table = 'quyen_hans';
    
    // Tắt timestamps
    public $timestamps = false;
    
    // Các trường fillable
    protected $fillable = ['ten','mo_ta','danh_sach_quyen','trang_thai'];
}


