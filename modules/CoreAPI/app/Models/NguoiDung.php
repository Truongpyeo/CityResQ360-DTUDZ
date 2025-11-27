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
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class NguoiDung extends Model
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $table = 'nguoi_dungs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'ho_ten',
        'email',
        'mat_khau',
        'so_dien_thoai',
        'vai_tro',
        'anh_dai_dien',
        'trang_thai',
        'diem_thanh_pho',
        'xac_thuc_cong_dan',
        'diem_uy_tin',
        'tong_so_phan_anh',
        'so_phan_anh_chinh_xac',
        'ty_le_chinh_xac',
        'cap_huy_hieu',
        'push_token',
        'tuy_chon_thong_bao',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'mat_khau',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'vai_tro' => 'integer',
            'trang_thai' => 'integer',
            'diem_thanh_pho' => 'integer',
            'xac_thuc_cong_dan' => 'boolean',
            'diem_uy_tin' => 'integer',
            'tong_so_phan_anh' => 'integer',
            'so_phan_anh_chinh_xac' => 'integer',
            'ty_le_chinh_xac' => 'float',
            'cap_huy_hieu' => 'integer',
            'tuy_chon_thong_bao' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Constants for vai_tro
     */
    const VAI_TRO_CITIZEN = 0;

    const VAI_TRO_OFFICER = 1;

    /**
     * Constants for trang_thai
     */
    const TRANG_THAI_BANNED = 0;

    const TRANG_THAI_ACTIVE = 1;

    /**
     * Constants for cap_huy_hieu
     */
    const HUY_HIEU_BRONZE = 0;

    const HUY_HIEU_SILVER = 1;

    const HUY_HIEU_GOLD = 2;

    const HUY_HIEU_PLATINUM = 3;

    /**
     * Relationships
     */
    public function phanAnhs(): HasMany
    {
        return $this->hasMany(PhanAnh::class, 'nguoi_dung_id');
    }

    public function binhLuans(): HasMany
    {
        return $this->hasMany(BinhLuanPhanAnh::class, 'nguoi_dung_id');
    }

    public function binhChons(): HasMany
    {
        return $this->hasMany(BinhChonPhanAnh::class, 'nguoi_dung_id');
    }

    /**
     * Check if user is active
     */
    public function isActive(): bool
    {
        return $this->trang_thai === self::TRANG_THAI_ACTIVE;
    }

    /**
     * Check if user is verified
     */
    public function isVerified(): bool
    {
        return $this->xac_thuc_cong_dan;
    }

    /**
     * Update accuracy rate
     */
    public function updateAccuracyRate(): void
    {
        if ($this->tong_so_phan_anh > 0) {
            $this->ty_le_chinh_xac = ($this->so_phan_anh_chinh_xac / $this->tong_so_phan_anh) * 100;
            $this->save();
        }
    }

    /**
     * Add city points
     */
    public function addCityPoints(int $points): void
    {
        $this->increment('diem_thanh_pho', $points);
    }

    /**
     * Deduct city points
     */
    public function deductCityPoints(int $points): void
    {
        $this->decrement('diem_thanh_pho', $points);
    }
}
