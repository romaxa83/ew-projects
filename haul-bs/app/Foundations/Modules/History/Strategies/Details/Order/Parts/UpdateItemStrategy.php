<?php

namespace App\Foundations\Modules\History\Strategies\Details\Order\Parts;

use App\Foundations\Modules\History\Strategies\Details\BaseDetailsStrategy;
use App\Models\Orders\Parts\Order;

class UpdateItemStrategy extends BaseDetailsStrategy
{
    public function __construct(
        protected Order $model,
        protected array $additional,
    )
    {}

    public function getDetails(): array
    {
        $item = $this->additional['item'];
        $oldQty = $this->additional['old_qty'] ?? null;
        $oldDiscount = $this->additional['old_discount'] ?? 0;

        $tmp["items.{$item->id}.inventories.{$item->inventory_id}.quantity"] = [
            'old' => $oldQty,
            'new' => $item->qty,
            'type' => self::TYPE_UPDATED
        ];
        $tmp["items.{$item->id}.inventories.{$item->inventory_id}.discount"] = [
            'old' => $oldDiscount,
            'new' => $item->discount,
            'type' => self::TYPE_UPDATED
        ];

        return $tmp;
    }
}
