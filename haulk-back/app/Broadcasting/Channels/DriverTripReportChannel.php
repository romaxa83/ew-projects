<?php


namespace App\Broadcasting\Channels;


use App\Broadcasting\Events\DriverTripReport\DriverTripReportCreateBroadcast;
use App\Broadcasting\Events\DriverTripReport\DriverTripReportDeleteBroadcast;
use App\Broadcasting\Events\DriverTripReport\DriverTripReportUpdateBroadcast;
use App\Models\Users\User;

class DriverTripReportChannel implements Channel
{
    public const NAME = 'driver-trip-reports.';
    public const PREFIX = 'private-';

    public static function getNameForUser(User $user): string
    {
        return self::NAME . $user->getCompanyId();
    }

    public function getPrefix(): string
    {
        return self::PREFIX;
    }

    public function getEvents(): array
    {
        return [
            DriverTripReportCreateBroadcast::NAME,
            DriverTripReportUpdateBroadcast::NAME,
            DriverTripReportDeleteBroadcast::NAME
        ];
    }

    public function isAllowedForUser(User $user): bool
    {
        return $user->can('driver-reports');
    }

    public function join(User $user, int $companyId): bool
    {
        return $user->getCompanyId() === $companyId && $this->isAllowedForUser($user);
    }
}
