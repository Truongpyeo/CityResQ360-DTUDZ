<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiemTransaction extends Model
{
    protected $table = 'diem_transactions';

    protected $fillable = [
        'diem_thuong_id',
        'nguoi_dung_id',
        'loai_giao_dich',
        'so_diem',
        'ly_do',
        'mo_ta',
        'lien_ket_den',
        'id_lien_ket',
        'so_du_truoc',
        'so_du_sau',
    ];

    protected $casts = [
        'so_diem' => 'integer',
        'so_du_truoc' => 'integer',
        'so_du_sau' => 'integer',
        'id_lien_ket' => 'integer',
    ];

    public function diemThuong(): BelongsTo
    {
        return $this->belongsTo(DiemThuong::class, 'diem_thuong_id');
    }

    public function nguoiDung(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'nguoi_dung_id');
    }
}
