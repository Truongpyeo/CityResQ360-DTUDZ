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
use Illuminate\Database\Eloquent\Relations\HasMany;

class PhanThuong extends Model
{
    protected $fillable = [
        'ten_phan_thuong',
        'mo_ta',
        'loai',
        'so_diem_can',
        'hinh_anh',
        'so_luong',
        'so_luong_con_lai',
        'ngay_het_han',
        'trang_thai',
    ];

    protected $casts = [
        'loai' => 'integer',
        'so_diem_can' => 'integer',
        'so_luong' => 'integer',
        'so_luong_con_lai' => 'integer',
        'trang_thai' => 'integer',
        'ngay_het_han' => 'date',
    ];

    /**
     * Get redemptions for this reward
     */
    public function doiPhanThuongs(): HasMany
    {
        return $this->hasMany(DoiPhanThuong::class, 'phan_thuong_id');
    }

    /**
     * Check if reward is available
     */
    public function isAvailable(): bool
    {
        return $this->trang_thai === 1 
            && $this->so_luong_con_lai > 0
            && ($this->ngay_het_han === null || $this->ngay_het_han->isFuture());
    }

    /**
     * Get reward type text
     */
    public function getLoaiTextAttribute(): string
    {
        $types = [
            0 => 'Voucher',
            1 => 'Quà tặng',
            2 => 'Dịch vụ',
        ];

        return $types[$this->loai] ?? 'Khác';
    }
}
