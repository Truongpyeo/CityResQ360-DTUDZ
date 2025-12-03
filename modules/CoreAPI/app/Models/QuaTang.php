<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuaTang extends Model
{
    protected $table = 'qua_tangs';

    protected $fillable = [
        'ten_qua_tang',
        'mo_ta',
        'hinh_anh',
        'so_diem_can',
        'so_luong_kho',
        'da_doi',
        'loai',
        'nha_tai_tro',
        'ngay_het_han',
        'trang_thai',
    ];

    protected $casts = [
        'so_diem_can' => 'integer',
        'so_luong_kho' => 'integer',
        'da_doi' => 'integer',
        'trang_thai' => 'boolean',
        'ngay_het_han' => 'date',
    ];
}
