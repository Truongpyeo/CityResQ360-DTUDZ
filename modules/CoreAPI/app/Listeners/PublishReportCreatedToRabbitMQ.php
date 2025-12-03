<?php

namespace App\Listeners;

use App\Events\ReportCreatedEvent;
use App\Services\RabbitMQService;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class PublishReportCreatedToRabbitMQ
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
    public function handle(ReportCreatedEvent $event): void
    {
        try {
            $this->rabbitMQ->publishReportEvent(
                'report.created',
                $event->report,
                $event->user
            );
            Log::info("Published report.created event for report ID: " . $event->report->id);
        } catch (\Exception $e) {
            Log::error("Failed to publish report.created event: " . $e->getMessage());
        }
    }
}
