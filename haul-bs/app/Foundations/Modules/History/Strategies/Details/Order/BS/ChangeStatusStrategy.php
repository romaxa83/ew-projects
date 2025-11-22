<?php

namespace App\Foundations\Modules\History\Strategies\Details\Order\BS;

use App\Foundations\Modules\History\Strategies\Details\BaseDetailsStrategy;
use App\Models\Orders\BS\Order;

class ChangeStatusStrategy extends BaseDetailsStrategy
{
    public function __construct(
        protected Order $model,
        protected array $additional,
    )
    {}

    public function getDetails(): array
    {
        if(!(isset($this->additional['old_status']))){
            throw new \Exception('[ChangeStatusStrategy] you need transfer a old_status');
        }

        $tmp['status'] = [
            'old' => $this->additional['old_status'],
            'new' => $this->model->status->value,
            'type' => self::TYPE_UPDATED
        ];

        return $tmp;
    }
}

