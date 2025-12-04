<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TinhThanh extends Model
{
    // Tên bảng
    protected $table = 'tinh_thanhs';
    
    // Tắt timestamps
    public $timestamps = false;
    
    // Các trường fillable
    protected $fillable = ['ten','ma'];
}


