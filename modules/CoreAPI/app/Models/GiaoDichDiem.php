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

class GiaoDichDiem extends Model
{
    // Constants for 'loai_giao_dich'
    const LOAI_CONG_DIEM = 0;
    const LOAI_TRU_DIEM = 1;
    const LOAI_DIEU_CHINH = 2;
    
    // Constants for 'trang_thai' (not in DB but used in logic/seeder)
    const TRANG_THAI_THANH_CONG = 1;

    protected $fillable = [
        'nguoi_dung_id',
        'loai_giao_dich',
        'so_diem',
        'so_du_truoc',
        'so_du_sau',
        'ly_do',
        'lien_ket_id',
        'lien_ket_loai',
    ];

    protected $casts = [
        'loai_giao_dich' => 'integer',
        'so_diem' => 'integer',
        'so_du_truoc' => 'integer',
        'so_du_sau' => 'integer',
    ];

    /**
     * Get the user that owns the transaction
     */
    public function nguoiDung(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'nguoi_dung_id');
    }

    /**
     * Get transaction type text
     */
    public function getLoaiGiaoDichTextAttribute(): string
    {
        $types = [
            0 => 'Thưởng',
            1 => 'Chi tiêu',
            2 => 'Admin điều chỉnh',
        ];

        return $types[$this->loai_giao_dich] ?? 'Không xác định';
    }
}
