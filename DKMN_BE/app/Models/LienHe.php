<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LienHe extends Model
{
    protected $table = 'lien_hes';
    
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
    
    const CREATED_AT = 'ngay_tao';
    const UPDATED_AT = 'ngay_cap_nhat';
    
    public function nguoiPhuTrach(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'nguoi_phu_trach_id');
    }
}
