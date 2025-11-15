<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuanTriVien extends Authenticatable
{
    use HasFactory, SoftDeletes;

    protected $table = 'quan_tri_viens';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'ten_quan_tri',
        'email',
        'mat_khau',
        'vai_tro',
        'anh_dai_dien',
        'trang_thai',
        'lan_dang_nhap_cuoi',
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
            'lan_dang_nhap_cuoi' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Constants for vai_tro
     */
    const VAI_TRO_SUPERADMIN = 0;
    const VAI_TRO_DATA_ADMIN = 1;
    const VAI_TRO_AGENCY_ADMIN = 2;

    /**
     * Constants for trang_thai
     */
    const TRANG_THAI_LOCKED = 0;
    const TRANG_THAI_ACTIVE = 1;

    /**
     * Check if admin is active
     */
    public function isActive(): bool
    {
        return $this->trang_thai === self::TRANG_THAI_ACTIVE;
    }

    /**
     * Check if admin is superadmin
     */
    public function isSuperAdmin(): bool
    {
        return $this->vai_tro === self::VAI_TRO_SUPERADMIN;
    }

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->mat_khau;
    }

    /**
     * Get the column name for the "remember me" token.
     *
     * @return string
     */
    public function getRememberTokenName()
    {
        return null; // Disable remember token if not using it
    }
}
