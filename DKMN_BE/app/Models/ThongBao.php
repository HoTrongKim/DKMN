<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ThongBao extends Model
{
    // Tên bảng
    protected $table = 'thong_baos';
    
    // Tắt timestamps
    public $timestamps = false;
    
    // Các trường fillable
    protected $fillable = ['nguoi_dung_id','tieu_de','noi_dung','loai','da_doc'];

    // Các hằng số loại thông báo
    public const LOAI_TRIP_UPDATE = 'trip_update';
    public const LOAI_INBOX = 'inbox';

    /**
     * Quan hệ n-1 với NguoiDung
     */
    public function nguoiDung()
    {
        return $this->belongsTo(NguoiDung::class, 'nguoi_dung_id');
    }
}
