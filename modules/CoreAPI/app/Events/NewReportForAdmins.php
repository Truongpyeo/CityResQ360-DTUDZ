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

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewReportForAdmins implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $report;
    public $user;

    /**
     * Create a new event instance.
     */
    public function __construct($report, $user)
    {
        $this->report = $report;
        $this->user = $user;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * Broadcast to public 'admin-reports' channel
     * All admins can subscribe to this channel
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('admin-reports'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'new.report';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'report' => [
                'id' => $this->report->id,
                'tieu_de' => $this->report->tieu_de,
                'mo_ta' => $this->report->mo_ta,
                'trang_thai' => $this->report->trang_thai,
                'dia_chi' => $this->report->dia_chi,
                'vi_do' => $this->report->vi_do,
                'kinh_do' => $this->report->kinh_do,
                'danh_muc' => $this->report->danhMuc ? [
                    'id' => $this->report->danhMuc->id,
                    'ten' => $this->report->danhMuc->ten_danh_muc,
                ] : null,
                'created_at' => $this->report->created_at->toISOString(),
            ],
            'user' => [
                'id' => $this->user->id,
                'ho_ten' => $this->user->ho_ten,
            ],
        ];
    }
}
