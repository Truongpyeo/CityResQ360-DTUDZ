<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Events\ReportCreatedEvent;
use App\Events\ReportUpdated;
use App\Events\ReportStatusChanged;
use App\Listeners\PublishReportCreatedToRabbitMQ;
use App\Listeners\PublishReportUpdatedToRabbitMQ;
use App\Listeners\PublishReportStatusChanged;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        ReportCreatedEvent::class => [
            PublishReportCreatedToRabbitMQ::class,
        ],
        ReportStatusChanged::class => [
            PublishReportStatusChanged::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
