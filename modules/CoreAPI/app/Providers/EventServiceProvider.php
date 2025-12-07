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

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Events\ReportCreatedEvent;
use App\Events\ReportUpdated;
use App\Events\ReportStatusChanged;
use App\Events\ReportApprovedEvent;
use App\Listeners\PublishReportCreatedToRabbitMQ;
use App\Listeners\PublishReportUpdatedToRabbitMQ;
use App\Listeners\PublishReportStatusChanged;
use App\Listeners\CreateIncidentFromReport;
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
            \App\Listeners\AwardPointsOnAdminConfirm::class, // ✅ NEW: Award points when admin confirms
        ],
        ReportApprovedEvent::class => [
            CreateIncidentFromReport::class, // ✅ Auto-create incident in IncidentService
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
