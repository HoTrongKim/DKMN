<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChiTietPhiDonHang extends Model
{
    // Tên bảng trong cơ sở dữ liệu
    protected $table = 'chi_tiet_phi_don_hangs';

    // Tắt timestamps
    public $timestamps = false;

    // Các trường có thể được gán giá trị hàng loạt
    protected $fillable = ['don_hang_id','phi_dich_vu_id','so_tien','mo_ta'];
}


