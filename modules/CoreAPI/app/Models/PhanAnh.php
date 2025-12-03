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

class PhanAnh extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'phan_anhs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nguoi_dung_id',
        'tieu_de',
        'mo_ta',
        'danh_muc_id',
        'trang_thai',
        'uu_tien_id',
        'vi_do',
        'kinh_do',
        'dia_chi',
        'nhan_ai',
        'do_tin_cay',
        'co_quan_phu_trach_id',
        'la_cong_khai',
        'luot_ung_ho',
        'luot_khong_ung_ho',
        'luot_xem',
        'han_phan_hoi',
        'thoi_gian_phan_hoi_thuc_te',
        'thoi_gian_giai_quyet',
        'danh_gia_hai_long',
        'la_trung_lap',
        'trung_lap_voi_id',
        'the_tags',
        'du_lieu_mo_rong',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'nguoi_dung_id' => 'integer',
            'danh_muc_id' => 'integer',
            'trang_thai' => 'integer',
            'uu_tien_id' => 'integer',
            'vi_do' => 'decimal:7',
            'kinh_do' => 'decimal:7',
            'nhan_ai' => 'array',
            'do_tin_cay' => 'float',
            'co_quan_phu_trach_id' => 'integer',
            'la_cong_khai' => 'boolean',
            'luot_ung_ho' => 'integer',
            'luot_khong_ung_ho' => 'integer',
            'luot_xem' => 'integer',
            'han_phan_hoi' => 'datetime',
            'thoi_gian_phan_hoi_thuc_te' => 'integer',
            'thoi_gian_giai_quyet' => 'integer',
            'danh_gia_hai_long' => 'integer',
            'la_trung_lap' => 'boolean',
            'trung_lap_voi_id' => 'integer',
            'the_tags' => 'array',
            'du_lieu_mo_rong' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Constants for danh_muc
     */
    const DANH_MUC_TRAFFIC = 0;
    const DANH_MUC_ENVIRONMENT = 1;
    const DANH_MUC_FIRE = 2;
    const DANH_MUC_WASTE = 3;
    const DANH_MUC_FLOOD = 4;
    const DANH_MUC_OTHER = 5;

    /**
     * Constants for trang_thai
     */
    const TRANG_THAI_PENDING = 0;
    const TRANG_THAI_VERIFIED = 1;
    const TRANG_THAI_IN_PROGRESS = 2;
    const TRANG_THAI_RESOLVED = 3;
    const TRANG_THAI_REJECTED = 4;

    /**
     * Constants for uu_tien
     */
    const UU_TIEN_LOW = 0;
    const UU_TIEN_MEDIUM = 1;
    const UU_TIEN_HIGH = 2;
    const UU_TIEN_URGENT = 3;

    /**
     * Relationships
     */
    public function nguoiDung(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'nguoi_dung_id');
    }

    public function danhMuc(): BelongsTo
    {
        return $this->belongsTo(DanhMucPhanAnh::class, 'danh_muc_id');
    }

    public function uuTien(): BelongsTo
    {
        return $this->belongsTo(MucUuTien::class, 'uu_tien_id');
    }

    public function coQuanXuLy(): BelongsTo
    {
        return $this->belongsTo(CoQuanXuLy::class, 'co_quan_phu_trach_id');
    }

    public function binhLuans(): HasMany
    {
        return $this->hasMany(BinhLuanPhanAnh::class, 'phan_anh_id');
    }

    public function hinhAnhs(): HasMany
    {
        return $this->hasMany(HinhAnhPhanAnh::class, 'phan_anh_id');
    }

    public function binhChons(): HasMany
    {
        return $this->hasMany(BinhChonPhanAnh::class, 'phan_anh_id');
    }

    public function phanAnhTrungLap(): BelongsTo
    {
        return $this->belongsTo(PhanAnh::class, 'trung_lap_voi_id');
    }

    public function cacPhanAnhTrungLap(): HasMany
    {
        return $this->hasMany(PhanAnh::class, 'trung_lap_voi_id');
    }

    /**
     * Scopes
     */
    public function scopePublic($query)
    {
        return $query->where('la_cong_khai', true);
    }

    public function scopeByCategory($query, int $categoryId)
    {
        return $query->where('danh_muc_id', $categoryId);
    }

    public function scopeByStatus($query, int $status)
    {
        return $query->where('trang_thai', $status);
    }

    public function scopeNearby($query, float $lat, float $lon, float $radiusKm = 5)
    {
        // Haversine formula for finding nearby locations
        $R = 6371; // Earth radius in km

        return $query->selectRaw("
            *,
            ($R * acos(
                cos(radians(?)) *
                cos(radians(vi_do)) *
                cos(radians(kinh_do) - radians(?)) +
                sin(radians(?)) *
                sin(radians(vi_do))
            )) AS distance
        ", [$lat, $lon, $lat])
            ->having('distance', '<=', $radiusKm)
            ->orderBy('distance');
    }

    /**
     * Methods
     */
    public function incrementViews(): void
    {
        $this->increment('luot_xem');
    }

    public function upvote(): void
    {
        $this->increment('luot_ung_ho');
    }

    public function downvote(): void
    {
        $this->increment('luot_khong_ung_ho');
    }

    public function getCategoryName(): string
    {
        return $this->danhMuc?->ten_danh_muc ?? 'Không xác định';
    }

    public function getStatusName(): string
    {
        return match ($this->trang_thai) {
            self::TRANG_THAI_PENDING => 'Chờ xử lý',
            self::TRANG_THAI_VERIFIED => 'Đã xác minh',
            self::TRANG_THAI_IN_PROGRESS => 'Đang xử lý',
            self::TRANG_THAI_RESOLVED => 'Đã giải quyết',
            self::TRANG_THAI_REJECTED => 'Từ chối',
            default => 'Không xác định',
        };
    }

    public function getPriorityName(): string
    {
        return $this->uuTien?->ten_muc ?? 'Không xác định';
    }

    public function isResolved(): bool
    {
        return $this->trang_thai === self::TRANG_THAI_RESOLVED;
    }

    public function isVerified(): bool
    {
        return $this->trang_thai === self::TRANG_THAI_VERIFIED;
    }
}
