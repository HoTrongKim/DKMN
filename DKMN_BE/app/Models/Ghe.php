<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ghe extends Model
{
    // Tên bảng
    protected $table = 'ghes';
    
    // Tắt timestamps
    public $timestamps = false;
    
    // Các trường fillable
    protected $fillable = ['chuyen_di_id','so_ghe','loai_ghe','gia','trang_thai'];

    /**
     * Quan hệ n-1 với ChuyenDi
     */
    public function chuyenDi(): BelongsTo
    {
        return $this->belongsTo(ChuyenDi::class, 'chuyen_di_id');
    }
}

