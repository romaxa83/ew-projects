<?php

namespace App\Enums\Inventories\Transaction;

enum DescribeType: string {
    case Sold = "sold";
    case Defect = "defect";
    case Broke = "broke";

    public function isSold(): bool
    {
        return $this === self::Sold;
    }
}

