<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CauHinhHeThong extends Model
{
    // Tên bảng trong cơ sở dữ liệu
    protected $table = 'cau_hinh_he_thongs';

    // Các trường có thể được gán giá trị hàng loạt (mass assignable)
    protected $fillable = ['khoa','gia_tri','mo_ta'];

    // Tên cột timestamp tùy chỉnh
    const CREATED_AT = 'ngay_tao';
    const UPDATED_AT = 'ngay_cap_nhat';
}


