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
use Illuminate\Database\Eloquent\SoftDeletes;

class ChucNang extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'chuc_nangs';

    protected $fillable = [
        'ten_chuc_nang',
        'route_name',
        'nhom_chuc_nang',
        'mo_ta',
        'trang_thai',
        'thu_tu',
    ];

    protected $casts = [
        'trang_thai' => 'integer',
        'thu_tu' => 'integer',
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
     * Function groups
     */
    const NHOM_DASHBOARD = 'dashboard';
    const NHOM_REPORTS = 'reports';
    const NHOM_USERS = 'users';
    const NHOM_AGENCIES = 'agencies';
    const NHOM_ANALYTICS = 'analytics';
    const NHOM_SETTINGS = 'settings';
    const NHOM_SYSTEM = 'system';

    /**
     * Relationship: Roles that have this permission
     */
    public function vaiTros()
    {
        return $this->belongsToMany(
            VaiTro::class,
            'chi_tiet_phan_quyens',
            'id_chuc_nang',
            'id_vai_tro'
        )->withTimestamps();
    }

    /**
     * Relationship: Permission details
     */
    public function chiTietPhanQuyens()
    {
        return $this->hasMany(ChiTietPhanQuyen::class, 'id_chuc_nang');
    }

    /**
     * Check if permission is active
     */
    public function isActive(): bool
    {
        return $this->trang_thai === self::TRANG_THAI_ACTIVE;
    }

    /**
     * Scope: Active permissions only
     */
    public function scopeActive($query)
    {
        return $query->where('trang_thai', self::TRANG_THAI_ACTIVE);
    }

    /**
     * Scope: By group
     */
    public function scopeByGroup($query, string $group)
    {
        return $query->where('nhom_chuc_nang', $group);
    }

    /**
     * Scope: Ordered
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('thu_tu')->orderBy('ten_chuc_nang');
    }
}
