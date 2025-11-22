<?php

namespace Core\Traits\Auth;

use App\Models\Dealers\Dealer;
use App\Models\Orders\Dealer\Order;
use Core\Exceptions\TranslatedException;

trait DealerInspector
{
    protected function isNotForMainDealer(): void
    {
//        dd($this->user() instanceof Dealer,$this->user()->isMain(),$this->user()->isMainCompany());

        if($this->user() instanceof Dealer && $this->user()->isMain() && !$this->user()->isMainCompany()){
            throw new TranslatedException(__("exceptions.dealer.not_action_for_main"), 502);
        }
    }

    protected function isCanMainCompany(): void
    {
        if($this->user() instanceof Dealer && !$this->user()->isMainCompany()){
            throw new TranslatedException(__("exceptions.dealer.not_action_for_main"), 502);
        }
    }

    protected function canUpdateOrder(Order $order):void
    {
        $this->isOwner($order);

        if(!$order->status->isDraft()){
            throw new TranslatedException(__("exceptions.dealer.order.order is not draft"), 502);
        }
    }

    protected function isOwner(Order $order): void
    {
        if(!$order->isOwner($this->user())){
            throw new TranslatedException(__("exceptions.dealer.order.can't this order"), 502);
        }
    }
}
