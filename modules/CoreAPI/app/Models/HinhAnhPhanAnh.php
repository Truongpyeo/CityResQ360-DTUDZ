<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class HinhAnhPhanAnh extends Model
{
    use SoftDeletes;

    protected $table = 'hinh_anh_phan_anhs';

    protected $fillable = [
        'nguoi_dung_id',
        'duong_dan_hinh_anh',
        'duong_dan_thumbnail',
        'loai_file',
        'kich_thuoc',
        'dinh_dang',
        'mo_ta',
        'media_service_id',
    ];

    protected function casts(): array
    {
        return [
            'kich_thuoc' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Relationship with NguoiDung
     */
    public function nguoiDung(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'nguoi_dung_id');
    }
}
