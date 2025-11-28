<?php
/*
 * CityResQ360-DTUDZ - Smart City Emergency Response System
 * Copyright (C) 2025 DTU-DZ Team
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */



namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class HinhAnhPhanAnh extends Model
{
    use SoftDeletes;

    protected $table = 'hinh_anh_phan_anhs';

    protected $fillable = [
        'phan_anh_id',
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

    /**
     * Relationship with PhanAnh
     */
    public function phanAnh(): BelongsTo
    {
        return $this->belongsTo(PhanAnh::class, 'phan_anh_id');
    }
}
