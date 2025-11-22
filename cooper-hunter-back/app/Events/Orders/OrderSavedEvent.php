<?php

namespace App\Events\Orders;

use App\Contracts\Alerts\AlertEvent;
use App\Contracts\Alerts\AlertModel;
use App\Contracts\Alerts\MetaDataDto;
use App\Contracts\Roles\HasGuardUser;
use App\Models\Orders\Order;
use Core\Traits\Auth\AuthGuardsTrait;

class OrderSavedEvent implements AlertEvent
{
    use AuthGuardsTrait;

    private HasGuardUser $user;

    public function __construct(private Order $order)
    {
        $user = $this->getAuthUser();

        if ($user !== null) {
            $this->user = $user;
        } else {
            $this->user = $this->order->technician;
        }
    }

    public function getOrder(): Order
    {
        return $this->order;
    }

    public function getUser(): HasGuardUser
    {
        return $this->user;
    }

    public function getInitiator(): ?HasGuardUser
    {
        return $this->getUser();
    }

    public function getModel(): AlertModel
    {
        return $this->getOrder();
    }

    public function isAlertEvent(): bool
    {
        return $this->order->wasRecentlyCreated || $this->order->wasChanged('status');
    }

    public function getMetaData(): ?MetaDataDto
    {
        return null;
    }
}
