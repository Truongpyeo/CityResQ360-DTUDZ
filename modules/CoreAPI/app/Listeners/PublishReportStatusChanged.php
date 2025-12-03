<?php

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
