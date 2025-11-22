<?php

namespace App\Enums\Inventories;

enum InventoryStockStatus: string {
    case IN = "in_stock";
    case OUT = "out_of_stock";

    public static function isIn(string $value): bool
    {
        return $value === InventoryStockStatus::IN->value;
    }

    public static function isOut(string $value): bool
    {
        return $value === InventoryStockStatus::OUT->value;
    }
}
