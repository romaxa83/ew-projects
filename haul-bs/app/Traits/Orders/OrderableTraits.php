<?php

namespace App\Traits\Orders;

/**
 * @property-read int id
 * @property-read string order_number
 */

trait OrderableTraits
{
    public function getId(): int
    {
        return $this->id;
    }

    public function getOrderNumber(): string
    {
        return $this->order_number;
    }
}


