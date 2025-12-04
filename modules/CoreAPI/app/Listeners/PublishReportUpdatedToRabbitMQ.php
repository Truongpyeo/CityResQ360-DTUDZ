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

use App\Events\ReportUpdated;
use App\Services\RabbitMQService;
use Illuminate\Support\Facades\Log;

class PublishReportUpdatedToRabbitMQ
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
    public function handle(ReportUpdated $event): void
    {
        try {
            $this->rabbitMQ->publishReportEvent(
                'report.updated',
                $event->report,
                $event->user
            );

            Log::info('Report updated event published', [
                'report_id' => $event->report->id,
                'changes' => $event->changes,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to publish report updated event', [
                'report_id' => $event->report->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
