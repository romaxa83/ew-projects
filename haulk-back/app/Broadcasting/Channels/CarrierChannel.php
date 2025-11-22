<?php


namespace App\Broadcasting\Channels;

use App\Broadcasting\Events\Carrier\DeleteCarrierBroadcast;
use App\Broadcasting\Events\Carrier\UpdateCarrierBroadcast;
use App\Broadcasting\Events\Carrier\UpdateCarrierInsuranceBroadcast;
use App\Broadcasting\Events\Carrier\UpdateCarrierNotificationBroadcast;
use App\Models\Users\User;

class CarrierChannel implements Channel
{
    public const NAME = 'carriers.';

    public const PREFIX = 'private-';

    public static function getNameForUser(User $user): string
    {
        return self::NAME . $user->getCompanyId();
    }

    public function getEvents(): array
    {
        return [
            DeleteCarrierBroadcast::NAME,
            UpdateCarrierBroadcast::NAME,
            UpdateCarrierNotificationBroadcast::NAME,
            UpdateCarrierInsuranceBroadcast::NAME
        ];
    }

    public function getPrefix(): string
    {
        return self::PREFIX;
    }

    public function isAllowedForUser(User $user): bool
    {
        return $user->can('company-settings');
    }

    public function join(User $user, int $companyId): bool
    {
        return $this->isAllowedForUser($user) && $user->getCompanyId() === $companyId;
    }
}
