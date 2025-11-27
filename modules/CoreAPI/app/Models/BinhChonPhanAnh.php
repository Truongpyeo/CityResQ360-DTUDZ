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

class BinhChonPhanAnh extends Model
{
    use HasFactory;

    protected $table = 'binh_chon_phan_anhs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'phan_anh_id',
        'nguoi_dung_id',
        'loai_binh_chon',
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
            'loai_binh_chon' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Constants for loai_binh_chon
     */
    const LOAI_UPVOTE = 0;
    const LOAI_DOWNVOTE = 1;

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

    /**
     * Scopes
     */
    public function scopeUpvotes($query)
    {
        return $query->where('loai_binh_chon', self::LOAI_UPVOTE);
    }

    public function scopeDownvotes($query)
    {
        return $query->where('loai_binh_chon', self::LOAI_DOWNVOTE);
    }

    /**
     * Methods
     */
    public function isUpvote(): bool
    {
        return $this->loai_binh_chon === self::LOAI_UPVOTE;
    }

    public function isDownvote(): bool
    {
        return $this->loai_binh_chon === self::LOAI_DOWNVOTE;
    }
}
