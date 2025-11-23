<?php

namespace Tests\Builders\Orders;

use App\Models\Order;
use App\Models\Order\InventoryActivity;
use Carbon\CarbonImmutable;
use Tests\Builders\BaseBuilder;

class InventoryActivityBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return InventoryActivity::class;
    }

    public function order(Order $model): self
    {
        $this->data['order_id'] = $model->id;
        return $this;
    }

    public function miscs(array $data): self
    {
        $this->data['miscs'] = $data;
        return $this;
    }

    public function created(CarbonImmutable $value): self
    {
        $this->data['created_at'] = $value;
        return $this;
    }
}
