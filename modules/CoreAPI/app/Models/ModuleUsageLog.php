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

class ModuleUsageLog extends Model
{
    use HasFactory;

    public $timestamps = false; // Only created_at

    protected $fillable = [
        'credential_id',
        'user_id',
        'module_id',
        'endpoint',
        'method',
        'status_code',
        'response_data',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'response_data' => 'array',
        'status_code' => 'integer',
        'created_at' => 'datetime',
    ];

    /**
     * Get the credential this log belongs to
     */
    public function credential(): BelongsTo
    {
        return $this->belongsTo(ClientModuleCredential::class, 'credential_id');
    }

    /**
     * Get the user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'user_id');
    }

    /**
     * Get the module
     */
    public function module(): BelongsTo
    {
        return $this->belongsTo(ModuleDefinition::class, 'module_id');
    }

    /**
     * Scope for successful requests
     */
    public function scopeSuccessful($query)
    {
        return $query->whereBetween('status_code', [200, 299]);
    }

    /**
     * Scope for failed requests
     */
    public function scopeFailed($query)
    {
        return $query->where('status_code', '>=', 400);
    }

    /**
     * Scope for recent logs
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
