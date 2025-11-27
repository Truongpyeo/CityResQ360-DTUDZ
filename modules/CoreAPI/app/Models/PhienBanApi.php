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

class PhienBanApi extends Model
{
    use HasFactory;

    protected $table = 'phien_ban_apis';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'phien_ban',
        'mo_ta',
        'ngay_phat_hanh',
        'ngay_het_han',
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
            'ngay_phat_hanh' => 'date',
            'ngay_het_han' => 'date',
            'trang_thai' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Constants for trang_thai
     */
    const TRANG_THAI_ACTIVE = 0;
    const TRANG_THAI_DEPRECATED = 1;
    const TRANG_THAI_UNSUPPORTED = 2;

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('trang_thai', self::TRANG_THAI_ACTIVE);
    }

    public function scopeDeprecated($query)
    {
        return $query->where('trang_thai', self::TRANG_THAI_DEPRECATED);
    }

    public function scopeCurrent($query)
    {
        return $query->active()
            ->where('ngay_phat_hanh', '<=', now())
            ->where(function ($q) {
                $q->whereNull('ngay_het_han')
                  ->orWhere('ngay_het_han', '>', now());
            });
    }

    /**
     * Methods
     */
    public function isActive(): bool
    {
        return $this->trang_thai === self::TRANG_THAI_ACTIVE;
    }

    public function isDeprecated(): bool
    {
        return $this->trang_thai === self::TRANG_THAI_DEPRECATED;
    }

    public function isUnsupported(): bool
    {
        return $this->trang_thai === self::TRANG_THAI_UNSUPPORTED;
    }

    public function isExpired(): bool
    {
        return $this->ngay_het_han && $this->ngay_het_han->isPast();
    }

    public function isCurrent(): bool
    {
        return $this->isActive()
            && $this->ngay_phat_hanh <= now()
            && (!$this->ngay_het_han || $this->ngay_het_han > now());
    }

    public function getStatusName(): string
    {
        return match($this->trang_thai) {
            self::TRANG_THAI_ACTIVE => 'Đang hoạt động',
            self::TRANG_THAI_DEPRECATED => 'Ngừng hỗ trợ',
            self::TRANG_THAI_UNSUPPORTED => 'Không còn hỗ trợ',
            default => 'Không xác định',
        };
    }

    /**
     * Static helper to get current API version
     */
    public static function getCurrentVersion(): ?self
    {
        return self::current()->orderByDesc('ngay_phat_hanh')->first();
    }

    /**
     * Static helper to check if a version is supported
     */
    public static function isVersionSupported(string $version): bool
    {
        $apiVersion = self::where('phien_ban', $version)->first();

        if (!$apiVersion) {
            return false;
        }

        return $apiVersion->isCurrent();
    }
}
