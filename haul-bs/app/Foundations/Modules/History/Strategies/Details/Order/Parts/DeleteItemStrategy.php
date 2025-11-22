<?php

namespace App\Foundations\Modules\History\Strategies\Details\Order\Parts;

use App\Foundations\Modules\History\Strategies\Details\BaseDetailsStrategy;
use App\Models\Orders\Parts\Order;

class DeleteItemStrategy extends BaseDetailsStrategy
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
            'old' => $inventory->name,
            'new' => null,
            'type' => self::TYPE_REMOVED
        ];
        $tmp["items.{$item->id}.inventories.{$item->inventory_id}.quantity"] = [
            'old' => $item->qty,
            'new' => null,
            'type' => self::TYPE_REMOVED
        ];

        return $tmp;
    }
}
