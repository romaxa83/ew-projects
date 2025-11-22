<?php

namespace App\Foundations\Modules\History\Strategies\Details\Inventory;

use App\Foundations\Modules\History\Strategies\Details\BaseDetailsStrategy;
use App\Models\Inventories\Inventory;

class QuantityReservedStrategy extends BaseDetailsStrategy
{
    public function __construct(
        protected Inventory $model,
        protected array $additional = [],
    )
    {}

    public function getDetails(): array
    {
        $attr = $this->model->getChanges();

        $tmp['quantity'] = [
            'old' => $this->additional['quantity'] + $attr['quantity'],
            'new' => $attr['quantity'],
            'type' => self::TYPE_UPDATED
        ];

        return $tmp;
    }
}
