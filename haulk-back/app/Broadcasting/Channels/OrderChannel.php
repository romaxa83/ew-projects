<?php


namespace App\Broadcasting\Channels;

use App\Broadcasting\Events\Orders\DeleteOrderBroadcast;
use App\Broadcasting\Events\Orders\NewOrderBroadcast;
use App\Broadcasting\Events\Orders\OrderChangeDeductBroadcast;
use App\Broadcasting\Events\Orders\OrderChangeIsPaidBroadcast;
use App\Broadcasting\Events\Orders\RestoreOrderBroadcast;
use App\Broadcasting\Events\Orders\UpdateOrderBroadcast;
use App\Models\Orders\Order;
use App\Models\Users\User;

class OrderChannel implements Channel
{
    public const NAME = 'orders.';

    public const PREFIX = 'private-';

    public static function getNameForUser(User $user): string
    {
        return self::NAME . $user->getCompanyId();
    }

    public function getEvents(): array
    {
        return [
            NewOrderBroadcast::NAME,
            UpdateOrderBroadcast::NAME,
            DeleteOrderBroadcast::NAME,
            RestoreOrderBroadcast::NAME,
            OrderChangeDeductBroadcast::NAME,
            OrderChangeIsPaidBroadcast::NAME
        ];
    }

    public function getPrefix(): string
    {
        return self::PREFIX;
    }

    public function isAllowedForUser(User $user): bool
    {
        return $user->can('viewList', Order::class);
    }

    public function join(User $user, int $companyId): bool
    {
        return $user->getCompanyId() === $companyId && $this->isAllowedForUser($user);
    }
}
