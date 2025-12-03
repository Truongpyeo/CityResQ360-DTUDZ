<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReportCreatedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $report;
    public $user;

    /**
     * Create a new event instance.
     */
    public function __construct($report, $user = null)
    {
        $this->report = $report;
        $this->user = $user;
    }
}
