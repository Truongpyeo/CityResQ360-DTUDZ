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

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DanhMucPhanAnh extends Model
{
    use HasFactory;

    protected $table = 'danh_muc_phan_anhs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'ten_danh_muc',
        'ma_danh_muc',
        'mo_ta',
        'icon',
        'mau_sac',
        'thu_tu_hien_thi',
        'trang_thai',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'thu_tu_hien_thi' => 'integer',
            'trang_thai' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Relationships
     */
    public function phanAnhs(): HasMany
    {
        return $this->hasMany(PhanAnh::class, 'danh_muc_id');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('trang_thai', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('thu_tu_hien_thi');
    }
}
