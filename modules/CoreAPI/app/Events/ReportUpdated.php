<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReportUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $report;
    public $user;
    public $changes;

    /**
     * Create a new event instance.
     */
    public function __construct($report, $changes = [], $user = null)
    {
        $this->report = $report;
        $this->changes = $changes;
        $this->user = $user;
    }
}
