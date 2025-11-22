<?php

namespace App\Foundations\Modules\History\Strategies\Details\Order\BS;

use App\Foundations\Modules\History\Strategies\Details\BaseDetailsStrategy;
use App\Models\Orders\BS\Order;
use App\Models\Users\User;

class ReassignMechanicStrategy extends BaseDetailsStrategy
{
    public function __construct(
        protected Order $model,
        protected array $additional,
    )
    {}

    public function getDetails(): array
    {
        if(!(isset($this->additional['old_mechanic']) && $this->additional['old_mechanic'] instanceof User)){
            throw new \Exception('[ReassignMechanicStrategy] you need transfer a old_mechanic');
        }

        $tmp['mechanic'] = [
            'old' => $this->additional['old_mechanic']->full_name,
            'new' => $this->model->mechanic->full_name,
            'type' => self::TYPE_UPDATED
        ];

        return $tmp;
    }
}

