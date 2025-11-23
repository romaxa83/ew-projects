<?php

namespace Tests\Builders\Orders;

use App\Models\Order;
use App\User;
use Carbon\CarbonImmutable;
use Tests\Builders\BaseBuilder;

class StatusHistoryBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return Order\StatusChangeHistory::class;
    }

    public function order_id(Order $model): self
    {
        $this->data['order_id'] = $model->id;
        return $this;
    }

    public function user_id(User $model): self
    {
        $this->data['user_id'] = $model->id;
        return $this;
    }

    public function prev_status(?Order\Status $model): self
    {
        $this->data['prev_status'] = is_null($model) ? 0 : $model->id;
        return $this;
    }

    public function new_status(Order\Status $model): self
    {
        $this->data['new_status'] = $model->id;
        return $this;
    }

    public function created_at(CarbonImmutable $value): self
    {
        $this->data['created_at'] = $value;
        return $this;
    }
}
