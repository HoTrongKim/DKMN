<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NhaVanHanh extends Model
{
    // Tên bảng
    protected $table = 'nha_van_hanhs';
    
    // Tắt timestamps
    public $timestamps = false;
    
    // Các trường fillable
    protected $fillable = ['ten','loai','mo_ta','lien_he_dien_thoai','lien_he_email','trang_thai'];
}


