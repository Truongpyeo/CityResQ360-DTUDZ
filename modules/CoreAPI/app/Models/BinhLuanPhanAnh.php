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
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BinhLuanPhanAnh extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'binh_luan_phan_anhs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'phan_anh_id',
        'nguoi_dung_id',
        'binh_luan_cha_id',
        'noi_dung',
        'la_chinh_thuc',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'phan_anh_id' => 'integer',
            'nguoi_dung_id' => 'integer',
            'binh_luan_cha_id' => 'integer',
            'la_chinh_thuc' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Relationships
     */
    public function phanAnh(): BelongsTo
    {
        return $this->belongsTo(PhanAnh::class, 'phan_anh_id');
    }

    public function nguoiDung(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'nguoi_dung_id');
    }

    public function binhLuanCha(): BelongsTo
    {
        return $this->belongsTo(BinhLuanPhanAnh::class, 'binh_luan_cha_id');
    }

    public function cacBinhLuanCon(): HasMany
    {
        return $this->hasMany(BinhLuanPhanAnh::class, 'binh_luan_cha_id');
    }

    /**
     * Scopes
     */
    public function scopeOfficial($query)
    {
        return $query->where('la_chinh_thuc', true);
    }

    public function scopeParentComments($query)
    {
        return $query->whereNull('binh_luan_cha_id');
    }

    /**
     * Methods
     */
    public function isOfficial(): bool
    {
        return $this->la_chinh_thuc;
    }

    public function isReply(): bool
    {
        return !is_null($this->binh_luan_cha_id);
    }
}
