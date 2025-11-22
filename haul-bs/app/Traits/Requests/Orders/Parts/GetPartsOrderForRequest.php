<?php

namespace App\Traits\Requests\Orders\Parts;

use App\Models\Orders\Parts\Order;
use App\Repositories\Orders\Parts\OrderRepository;

trait GetPartsOrderForRequest
{
    protected Order|null $order = null;

    public function getOrder(): Order
    {
        if(!$this->order){
            /** @var $repo OrderRepository */
            $repo = resolve(OrderRepository::class);

            /** @var $model Order */
            $this->order = $repo->getById($this->route('id'));
        }

        return $this->order;
    }
}

