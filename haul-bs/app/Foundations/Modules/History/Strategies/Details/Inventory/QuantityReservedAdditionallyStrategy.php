<?php

namespace App\Foundations\Modules\History\Strategies\Details\Inventory;

use App\Foundations\Modules\History\Strategies\Details\BaseDetailsStrategy;
use App\Models\Inventories\Inventory;

class QuantityReservedAdditionallyStrategy extends BaseDetailsStrategy
{
    public function __construct(
        protected Inventory $model,
        protected array $additional = [],
    )
    {}

    public function getDetails(): array
    {
        $tmp['quantity'] = [
            'old' => $this->additional['old_quantity'],
            'new' => $this->additional['quantity'],
            'type' => self::TYPE_UPDATED
        ];

        return $tmp;
    }
}
