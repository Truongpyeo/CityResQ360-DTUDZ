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
