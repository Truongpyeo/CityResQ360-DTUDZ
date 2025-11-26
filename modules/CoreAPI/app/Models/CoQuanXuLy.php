<?php

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
