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
