<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VaiTro extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'vai_tros';

    protected $fillable = [
        'ten_vai_tro',
        'slug',
        'mo_ta',
        'trang_thai',
    ];

    protected $casts = [
        'trang_thai' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Constants
     */
    const TRANG_THAI_LOCKED = 0;
    const TRANG_THAI_ACTIVE = 1;

    /**
     * Predefined role slugs
     */
    const SLUG_SUPER_ADMIN = 'super-admin';
    const SLUG_DATA_ADMIN = 'data-admin';
    const SLUG_AGENCY_ADMIN = 'agency-admin';
    const SLUG_MODERATOR = 'moderator';
    const SLUG_VIEWER = 'viewer';

    /**
     * Relationship: Admins with this role
     */
    public function admins()
    {
        return $this->hasMany(QuanTriVien::class, 'id_vai_tro');
    }

    /**
     * Relationship: Permissions through pivot table
     */
    public function chucNangs()
    {
        return $this->belongsToMany(
            ChucNang::class,
            'chi_tiet_phan_quyens',
            'id_vai_tro',
            'id_chuc_nang'
        )->withTimestamps();
    }

    /**
     * Relationship: Permission details
     */
    public function chiTietPhanQuyens()
    {
        return $this->hasMany(ChiTietPhanQuyen::class, 'id_vai_tro');
    }

    /**
     * Check if role is active
     */
    public function isActive(): bool
    {
        return $this->trang_thai === self::TRANG_THAI_ACTIVE;
    }

    /**
     * Check if role has permission
     */
    public function hasPermission(string $routeName): bool
    {
        return $this->chucNangs()
            ->where('route_name', $routeName)
            ->where('trang_thai', ChucNang::TRANG_THAI_ACTIVE)
            ->exists();
    }

    /**
     * Get all permissions
     */
    public function getPermissions()
    {
        return $this->chucNangs()
            ->where('trang_thai', ChucNang::TRANG_THAI_ACTIVE)
            ->pluck('route_name')
            ->toArray();
    }

    /**
     * Scope: Active roles only
     */
    public function scopeActive($query)
    {
        return $query->where('trang_thai', self::TRANG_THAI_ACTIVE);
    }
}
