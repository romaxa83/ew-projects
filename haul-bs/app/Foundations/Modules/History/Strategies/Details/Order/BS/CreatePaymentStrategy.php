<?php

namespace App\Foundations\Modules\History\Strategies\Details\Order\BS;

use App\Foundations\Modules\History\Strategies\Details\BaseDetailsStrategy;
use App\Models\Orders\BS\Order;
use App\Models\Orders\BS\Payment;

class CreatePaymentStrategy extends BaseDetailsStrategy
{
    public function __construct(
        protected Order $model,
        protected array $additional,
    )
    {}

    private function exclude(): array
    {
        return [
            'id',
            'order_id',
            'payment_date',
        ];
    }

    public function getDetails(): array
    {
        if(!(isset($this->additional['payment']) && $this->additional['payment'] instanceof Payment)){
            throw new \Exception('[CreatePaymentStrategy] you need transfer a payment entity');
        }

        $attr = $this->additional['payment']->getAttributes();
        $id = $attr['id'];

        foreach ($this->exclude() as $key){
            unset($attr[$key]);
        }

        $tmp = [];
        foreach ($attr as $k => $value){
            if($value === null) continue;

            $tmp['payments.'.$id.'.'.$k] = [
                'old' => null,
                'new' => $value,
                'type' => self::TYPE_ADDED
            ];
        }

        return $tmp;
    }
}

