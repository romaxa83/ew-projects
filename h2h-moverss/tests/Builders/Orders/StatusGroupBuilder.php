<?php

namespace Tests\Builders\Orders;

use App\Models\Order;
use Tests\Builders\BaseBuilder;

class StatusGroupBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return Order\StatusGroup::class;
    }

    public function sort(int $value): self
    {
        $this->data['sort'] = $value;
        return $this;
    }

    public function in_funel_report(int $value): self
    {
        $this->data['in_funel_report'] = $value;
        return $this;
    }
}
