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
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Hash;

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
        'id_vai_tro',
        'is_master',
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
            'id_vai_tro' => 'integer',
            'is_master' => 'boolean',
            'trang_thai' => 'integer',
            'lan_dang_nhap_cuoi' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

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
     * Check if admin is master (Super Admin with full access)
     */
    public function isMaster(): bool
    {
        return $this->is_master === true;
    }

    /**
     * Relationship: Role
     */
    public function vaiTro()
    {
        return $this->belongsTo(VaiTro::class, 'id_vai_tro');
    }

    /**
     * Relationship: Admin logs
     */
    public function logs()
    {
        return $this->hasMany(NhatKyHeThong::class, 'nguoi_thuc_hien_id')
            ->where('loai_nguoi_thuc_hien', 'admin');
    }

    /**
     * Check if admin has permission to access route
     */
    public function hasPermission(string $routeName): bool
    {
        // Master admin has all permissions
        if ($this->is_master) {
            return true;
        }

        // Check role permissions
        if ($this->vaiTro) {
            return $this->vaiTro->hasPermission($routeName);
        }

        return false;
    }

    /**
     * Get all permissions
     */
    public function getPermissions(): array
    {
        // Master admin has all permissions
        if ($this->is_master) {
            return ['*']; // Wildcard means all permissions
        }

        // Get role permissions
        if ($this->vaiTro) {
            return $this->vaiTro->getPermissions();
        }

        return [];
    }

    /**
     * Check if has any of the permissions
     */
    public function hasAnyPermission(array $routeNames): bool
    {
        if ($this->is_master) {
            return true;
        }

        foreach ($routeNames as $routeName) {
            if ($this->hasPermission($routeName)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if has all permissions
     */
    public function hasAllPermissions(array $routeNames): bool
    {
        if ($this->is_master) {
            return true;
        }

        foreach ($routeNames as $routeName) {
            if (!$this->hasPermission($routeName)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get role name
     */
    public function getRoleName(): string
    {
        if ($this->is_master) {
            return 'Super Admin (Master)';
        }

        return $this->vaiTro ? $this->vaiTro->ten_vai_tro : 'Chưa có vai trò';
    }

    /**
     * Set password with bcrypt (auto-hash plain text passwords)
     */
    public function setMatKhauAttribute($value)
    {
        // If value is not already hashed, hash it
        // Hash::needsRehash returns false if already hashed
        if (!empty($value)) {
            $this->attributes['mat_khau'] = Hash::needsRehash($value)
                ? Hash::make($value)
                : $value;
        }
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

    /**
     * Scope: Only master admins
     */
    public function scopeMaster($query)
    {
        return $query->where('is_master', true);
    }

    /**
     * Scope: Active admins only
     */
    public function scopeActive($query)
    {
        return $query->where('trang_thai', self::TRANG_THAI_ACTIVE);
    }
}
