<?php

namespace App\Contracts\Orders;

use Illuminate\Database\Eloquent\Relations\MorphMany;

interface Orderable
{
    public function getId(): int;
    public function getOrderNumber(): string;

    public function isPartsOrder(): bool;
}

