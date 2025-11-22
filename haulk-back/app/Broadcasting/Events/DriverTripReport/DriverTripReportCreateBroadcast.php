<?php


namespace App\Broadcasting\Events\DriverTripReport;

class DriverTripReportCreateBroadcast extends DriverTripReportBroadcast
{
    public const NAME = 'driver-trip-report.create';

    protected function getName(): string
    {
        return self::NAME;
    }
}
