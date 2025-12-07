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

class ReportStatusUpdatedForUsers implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $report;
    public $oldStatus;
    public $newStatus;

    /**
     * Create a new event instance.
     */
    public function __construct($report, $oldStatus, $newStatus)
    {
        $this->report = $report;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * Broadcast to public 'user-reports' channel
     * All mobile users can subscribe to auto-refresh map
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('user-reports'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'report.status.updated';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        $statusText = [
            0 => 'Chờ xử lý',
            1 => 'Đã xác nhận',
            2 => 'Đang xử lý',
            3 => 'Đã giải quyết',
            4 => 'Từ chối',
        ];

        return [
            'report_id' => $this->report->id,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'status_text' => $statusText[$this->newStatus] ?? 'Unknown',
            'report' => [
                'id' => $this->report->id,
                'tieu_de' => $this->report->tieu_de,
                'trang_thai' => $this->report->trang_thai,
                'dia_chi' => $this->report->dia_chi,
                'vi_do' => $this->report->vi_do,
                'kinh_do' => $this->report->kinh_do,
                'updated_at' => $this->report->updated_at->toISOString(),
            ],
        ];
    }
}
