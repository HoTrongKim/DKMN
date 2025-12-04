<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LienHe extends Model
{
    // Tên bảng
    protected $table = 'lien_hes';
    
    // Các trường fillable
    protected $fillable = [
        'ho_ten',
        'email',
        'so_dien_thoai',
        'chu_de',
        'noi_dung',
        'trang_thai',
        'tra_loi',
        'nguoi_phu_trach_id',
        'ngay_tra_loi',
    ];
    
    // Tên cột timestamp tùy chỉnh
    const CREATED_AT = 'ngay_tao';
    const UPDATED_AT = 'ngay_cap_nhat';
    
    /**
     * Quan hệ n-1 với NguoiDung (Người phụ trách trả lời)
     */
    public function nguoiPhuTrach(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'nguoi_phu_trach_id');
    }
}
