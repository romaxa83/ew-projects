<?php

namespace App\Foundations\Modules\History\Strategies\Details\Order\Parts;

use App\Foundations\Modules\History\Strategies\Details\BaseDetailsStrategy;
use App\Models\Orders\Parts\Order;

class AssignSalesManagerStrategy extends BaseDetailsStrategy
{
    public function __construct(
        protected Order $model,
        protected array $additional,
    )
    {}

    public function getDetails(): array
    {
        $tmp['sales_manager'] = [
            'old' => $this->additional['old_sales_manager']?->full_name,
            'new' => $this->model->salesManager->full_name,
            'type' => self::TYPE_UPDATED
        ];
        if($this->additional['old_status'] != $this->model->status){
            $tmp['status'] = [
                'old' => $this->additional['old_status']->value,
                'new' => $this->model->status->value,
                'type' => self::TYPE_UPDATED
            ];
        }

        return $tmp;
    }
}
