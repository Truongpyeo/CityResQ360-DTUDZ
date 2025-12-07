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

use App\Models\PhanAnh;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event fired when a report is approved (VERIFIED)
 * This triggers automatic incident creation in IncidentService
 */
class ReportApprovedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public PhanAnh $report;
    public ?int $approvedByUserId;
    public ?string $notes;

    /**
     * Create a new event instance.
     */
    public function __construct(PhanAnh $report, ?int $approvedByUserId = null, ?string $notes = null)
    {
        $this->report = $report;
        $this->approvedByUserId = $approvedByUserId;
        $this->notes = $notes;
    }
}
