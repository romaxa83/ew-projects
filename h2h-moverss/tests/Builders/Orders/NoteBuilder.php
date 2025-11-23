<?php

namespace Tests\Builders\Orders;

use App\Models\Order;
use App\User;
use Carbon\CarbonImmutable;
use Tests\Builders\BaseBuilder;

class NoteBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return Order\Notes::class;
    }

    public function is_pinned(bool $value): self
    {
        $this->data['is_pinned'] = $value;
        return $this;
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
}
