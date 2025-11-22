<?php


namespace App\Broadcasting\Events\DriverTripReport;

class DriverTripReportUpdateBroadcast extends DriverTripReportBroadcast
{
    public const NAME = 'driver-trip-report.update';

    protected function getName(): string
    {
        return self::NAME;
    }
}
