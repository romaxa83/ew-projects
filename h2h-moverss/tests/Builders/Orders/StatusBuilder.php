<?php

namespace Tests\Builders\Orders;

use App\Models\Order;
use Tests\Builders\BaseBuilder;

class StatusBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return Order\Status::class;
    }

    public function id(int $value): self
    {
        $this->data['id'] = $value;
        return $this;
    }

    public function group(?Order\StatusGroup $model): self
    {
        $this->data['group_id'] = is_null($model) ? null : $model->id;
        return $this;
    }

    public function actions($value): self
    {
        $this->data['actions'] = $value;
        return $this;
    }

    public function asNewLead(): self
    {
        return $this->id(Order\Status::NEW_LEAD_ID);
    }

    public function asBooked(): self
    {
        return $this->id(Order\Status::BOOKED_ID);
    }

    public function asDuplicate(): self
    {
        return $this->id(Order\Status::DUPLICATE_ID);
    }

    public function asSuccess(): self
    {
        return $this->id(Order\Status::SUCCESS_ID);
    }

    public function asLost(): self
    {
        return $this->id(Order\Status::LOST_ID);
    }

    public function asDone(): self
    {
        return $this->id(Order\Status::CALCULATED_DONE_ID);
    }

    public function asSalesDone(): self
    {
        return $this->id(Order\Status::SALES_DONE_ID);
    }
}
