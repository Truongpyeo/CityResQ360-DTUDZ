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

use App\Events\ReportApprovedEvent;
use App\Services\IncidentServiceClient;
use Illuminate\Support\Facades\Log;

/**
 * Listener that creates an incident in IncidentService when a report is approved
 */
class CreateIncidentFromReport
{
    private IncidentServiceClient $incidentService;

    /**
     * Create the event listener.
     */
    public function __construct(IncidentServiceClient $incidentService)
    {
        $this->incidentService = $incidentService;
    }

    /**
     * Handle the event.
     */
    public function handle(ReportApprovedEvent $event): void
    {
        $report = $event->report;

        Log::info('CreateIncidentFromReport: Processing approved report', [
            'report_id' => $report->id,
            'title' => $report->tieu_de,
            'priority' => $report->uu_tien_id,
        ]);

        // Check if incident already exists (avoid duplicates)
        if ($report->incident_id) {
            Log::warning('CreateIncidentFromReport: Incident already exists', [
                'report_id' => $report->id,
                'incident_id' => $report->incident_id,
            ]);
            return;
        }

        // Prepare incident data
        $incidentData = [
            'report_id' => $report->id,
            'priority' => IncidentServiceClient::mapPriority($report->uu_tien_id),
            'notes' => $event->notes ?? "Auto-created from approved Report #{$report->id}: {$report->tieu_de}",
        ];

        // Optionally assign to agency if already set in report
        if ($report->co_quan_phu_trach_id) {
            $incidentData['assigned_agency_id'] = $report->co_quan_phu_trach_id;
        }

        // Create incident in IncidentService
        $incident = $this->incidentService->createIncident($incidentData);

        if ($incident && isset($incident['id'])) {
            // Save incident_id back to report
            $report->update(['incident_id' => $incident['id']]);

            Log::info('CreateIncidentFromReport: Incident created successfully', [
                'report_id' => $report->id,
                'incident_id' => $incident['id'],
                'status' => $incident['status'] ?? 'unknown',
            ]);
        } else {
            Log::error('CreateIncidentFromReport: Failed to create incident', [
                'report_id' => $report->id,
            ]);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(ReportApprovedEvent $event, \Throwable $exception): void
    {
        Log::error('CreateIncidentFromReport: Listener failed', [
            'report_id' => $event->report->id,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
