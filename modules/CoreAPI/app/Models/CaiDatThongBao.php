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

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CaiDatThongBao extends Model
{
    protected $fillable = [
        'nguoi_dung_id',
        'email_enabled',
        'push_enabled',
        'report_status_update',
        'report_assigned',
        'comment_reply',
        'system_announcement',
    ];

    protected $casts = [
        'email_enabled' => 'boolean',
        'push_enabled' => 'boolean',
        'report_status_update' => 'boolean',
        'report_assigned' => 'boolean',
        'comment_reply' => 'boolean',
        'system_announcement' => 'boolean',
    ];

    /**
     * Get the user
     */
    public function nguoiDung(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'nguoi_dung_id');
    }

    /**
     * Check if notification type is enabled
     */
    public function isEnabled(string $type): bool
    {
        return $this->$type ?? false;
    }

    /**
     * Get or create settings for user
     */
    public static function getOrCreate(int $userId): self
    {
        return static::firstOrCreate(
            ['nguoi_dung_id' => $userId],
            [
                'email_enabled' => true,
                'push_enabled' => true,
                'report_status_update' => true,
                'report_assigned' => true,
                'comment_reply' => true,
                'system_announcement' => true,
            ]
        );
    }
}
