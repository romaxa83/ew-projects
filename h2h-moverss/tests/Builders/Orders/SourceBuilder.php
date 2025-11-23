<?php

namespace Tests\Builders\Orders;

use App\Models\Order;
use Tests\Builders\BaseBuilder;

class SourceBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return Order\Source::class;
    }
}
