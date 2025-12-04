<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tram extends Model
{
    // Tên bảng
    protected $table = 'trams';
    
    // Tắt timestamps
    public $timestamps = false;
    
    // Các trường fillable
    protected $fillable = ['ten','tinh_thanh_id','loai','dia_chi'];

    /**
     * Quan hệ n-1 với TinhThanh
     */
    public function tinhThanh(): BelongsTo
    {
        return $this->belongsTo(TinhThanh::class, 'tinh_thanh_id');
    }
}

