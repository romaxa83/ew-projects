<?php


namespace App\Broadcasting\Channels;

use App\Broadcasting\Events\Payroll\PayrollCreateBroadcast;
use App\Broadcasting\Events\Payroll\PayrollDeleteBroadcast;
use App\Broadcasting\Events\Payroll\PayrollMarkIsPaidBroadcast;
use App\Broadcasting\Events\Payroll\PayrollUpdateBroadcast;
use App\Models\Users\User;

class PayrollChannel implements Channel
{
    public const NAME = 'payrolls.';
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
            PayrollDeleteBroadcast::NAME,
            PayrollCreateBroadcast::NAME,
            PayrollUpdateBroadcast::NAME,
            PayrollMarkIsPaidBroadcast::NAME
        ];
    }

    public function isAllowedForUser(User $user): bool
    {
        return $user->can('payrolls');
    }

    public function join(User $user, int $companyId): bool
    {
        return $user->getCompanyId() === $companyId && $this->isAllowedForUser($user);
    }

}
