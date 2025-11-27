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
use Illuminate\Database\Eloquent\SoftDeletes;

class CoQuanXuLy extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'co_quan_xu_lys';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'ten_co_quan',
        'email_lien_he',
        'so_dien_thoai',
        'dia_chi',
        'cap_do',
        'mo_ta',
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
            'cap_do' => 'integer',
            'trang_thai' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Constants for cap_do
     */
    const CAP_DO_WARD = 0;
    const CAP_DO_DISTRICT = 1;
    const CAP_DO_CITY = 2;

    /**
     * Constants for trang_thai
     */
    const TRANG_THAI_INACTIVE = 0;
    const TRANG_THAI_ACTIVE = 1;

    /**
     * Relationships
     */
    public function phanAnhs(): HasMany
    {
        return $this->hasMany(PhanAnh::class, 'co_quan_phu_trach_id');
    }

    /**
     * Check if agency is active
     */
    public function isActive(): bool
    {
        return $this->trang_thai === self::TRANG_THAI_ACTIVE;
    }

    /**
     * Get level name
     */
    public function getLevelName(): string
    {
        return match($this->cap_do) {
            self::CAP_DO_WARD => 'Phường/Xã',
            self::CAP_DO_DISTRICT => 'Quận/Huyện',
            self::CAP_DO_CITY => 'Thành phố',
            default => 'Không xác định',
        };
    }
}
