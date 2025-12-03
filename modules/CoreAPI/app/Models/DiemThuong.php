<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DiemThuong extends Model
{
    protected $table = 'diem_thuongs';

    protected $fillable = [
        'nguoi_dung_id',
        'so_du_hien_tai',
        'tong_diem_kiem_duoc',
        'tong_diem_da_tieu',
    ];

    protected $casts = [
        'so_du_hien_tai' => 'integer',
        'tong_diem_kiem_duoc' => 'integer',
        'tong_diem_da_tieu' => 'integer',
    ];

    // Relationships
    public function nguoiDung(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'nguoi_dung_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(DiemTransaction::class, 'diem_thuong_id');
    }
}
