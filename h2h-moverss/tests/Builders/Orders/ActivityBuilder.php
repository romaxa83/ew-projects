<?php

namespace Tests\Builders\Orders;

use App\Enums\Orders\ActivityType;
use App\Models\Order;
use App\User;
use Carbon\CarbonImmutable;
use Tests\Builders\BaseBuilder;

class ActivityBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return Order\Activity::class;
    }

    public function user(User $model): self
    {
        $this->data['user_id'] = $model->id;
        return $this;
    }

    public function order(Order $model): self
    {
        $this->data['order_id'] = $model->id;
        return $this;
    }

    public function created_at(CarbonImmutable $value): self
    {
        $this->data['created_at'] = $value;
        return $this;
    }

    public function updated_at(CarbonImmutable $value): self
    {
        $this->data['updated_at'] = $value;
        return $this;
    }

    public function type(ActivityType $value): self
    {
        $this->data['type'] = $value->value;
        return $this;
    }

    public function miscs(array $value): self
    {
        $this->data['miscs'] = $value;
        return $this;
    }
}
