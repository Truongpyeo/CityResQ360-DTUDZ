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
