<?php


namespace App\Services\Events\Reports;


use App\Broadcasting\Events\DriverTripReport\DriverTripReportCreateBroadcast;
use App\Broadcasting\Events\DriverTripReport\DriverTripReportDeleteBroadcast;
use App\Broadcasting\Events\DriverTripReport\DriverTripReportUpdateBroadcast;
use App\Models\Reports\DriverTripReport;
use App\Services\Events\EventService;

class DriverTripReportEventService extends EventService
{
    private const BROADCASTING_EVENTS = [
        self::ACTION_CREATE => DriverTripReportCreateBroadcast::class,
        self::ACTION_DELETE => DriverTripReportDeleteBroadcast::class,
        self::ACTION_UPDATE => DriverTripReportUpdateBroadcast::class
    ];

    private DriverTripReport $report;

    public function __construct(DriverTripReport $report)
    {
        $report->refresh();

        $this->report = $report;
    }

    public function broadcast(): DriverTripReportEventService
    {
        $broadcast = self::BROADCASTING_EVENTS[$this->action];

        event(new $broadcast($this->report->id, $this->user->getCompanyId()));

        return $this;
    }
}
