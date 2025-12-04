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

namespace App\Listeners;

use App\Events\ReportStatusChanged;
use App\Services\RabbitMQService;
use Illuminate\Support\Facades\Log;

class PublishReportStatusChanged
{

    protected $rabbitMQ;

    /**
     * Create the event listener.
     */
    public function __construct(RabbitMQService $rabbitMQ)
    {
        $this->rabbitMQ = $rabbitMQ;
    }

    /**
     * Handle the event.
     */
    public function handle(ReportStatusChanged $event): void
    {
        try {
            $data = [
                'event' => 'report.status.changed',
                'report_id' => $event->report->id,
                'old_status' => $event->oldStatus,
                'new_status' => $event->newStatus,
                'user_id' => $event->user?->id,
                'timestamp' => now()->toIso8601String(),
            ];

            $this->rabbitMQ->publish('cityresq.reports', 'report.status.changed', $data);

            Log::info('Report status changed event published', [
                'report_id' => $event->report->id,
                'old_status' => $event->oldStatus,
                'new_status' => $event->newStatus,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to publish report status changed event', [
                'report_id' => $event->report->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
