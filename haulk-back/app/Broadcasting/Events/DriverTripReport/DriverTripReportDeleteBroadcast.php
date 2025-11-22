<?php


namespace App\Broadcasting\Events\DriverTripReport;

class DriverTripReportDeleteBroadcast extends DriverTripReportBroadcast
{
    public const NAME = 'driver-trip-report.delete';

    protected function getName(): string
    {
        return self::NAME;
    }
}
