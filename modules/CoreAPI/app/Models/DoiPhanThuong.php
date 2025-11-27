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

class DoiPhanThuong extends Model
{
    protected $fillable = [
        'nguoi_dung_id',
        'phan_thuong_id',
        'so_diem_su_dung',
        'ma_voucher',
        'trang_thai',
        'ngay_su_dung',
    ];

    protected $casts = [
        'so_diem_su_dung' => 'integer',
        'trang_thai' => 'integer',
        'ngay_su_dung' => 'datetime',
    ];

    /**
     * Get the user who redeemed
     */
    public function nguoiDung(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'nguoi_dung_id');
    }

    /**
     * Get the reward
     */
    public function phanThuong(): BelongsTo
    {
        return $this->belongsTo(PhanThuong::class, 'phan_thuong_id');
    }

    /**
     * Get status text
     */
    public function getTrangThaiTextAttribute(): string
    {
        $statuses = [
            0 => 'Chờ duyệt',
            1 => 'Đã duyệt',
            2 => 'Đã sử dụng',
            3 => 'Hết hạn',
        ];

        return $statuses[$this->trang_thai] ?? 'Không xác định';
    }
}
