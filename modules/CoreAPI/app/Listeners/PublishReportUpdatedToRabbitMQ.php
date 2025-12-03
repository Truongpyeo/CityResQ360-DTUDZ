<?php

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
