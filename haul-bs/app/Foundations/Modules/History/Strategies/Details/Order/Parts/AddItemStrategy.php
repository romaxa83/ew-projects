<?php

namespace App\Foundations\Modules\History\Strategies\Details\Order\Parts;

use App\Foundations\Modules\History\Strategies\Details\BaseDetailsStrategy;
use App\Models\Orders\Parts\Order;

class AddItemStrategy extends BaseDetailsStrategy
{
    public function __construct(
        protected Order $model,
        protected array $additional,
    )
    {}

    public function getDetails(): array
    {
        $item = $this->additional['item'];
        $inventory = $this->additional['inventory'];

        $tmp["items.{$item->id}.inventories.{$item->inventory_id}.name"] = [
            'old' => null,
            'new' => $inventory->name,
            'type' => self::TYPE_ADDED
        ];
        $tmp["items.{$item->id}.inventories.{$item->inventory_id}.quantity"] = [
            'old' => null,
            'new' => $item->qty,
            'type' => self::TYPE_ADDED
        ];
        $tmp["items.{$item->id}.inventories.{$item->inventory_id}.discount"] = [
            'old' => null,
            'new' => $item->discount,
            'type' => self::TYPE_ADDED
        ];

        return $tmp;
    }
}
