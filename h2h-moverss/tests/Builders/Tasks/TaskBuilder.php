<?php

namespace Tests\Builders\Tasks;

use App\Models\Division;
use App\Models\Order;
use App\Models\Tasks;
use App\User;
use Carbon\CarbonImmutable;
use Tests\Builders\BaseBuilder;

class TaskBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return Tasks\Task::class;
    }

    public function division(?Division $model): self
    {
        $this->data['division_id'] = $model->id ?? null;
        return $this;
    }

    public function status(Tasks\Status $model): self
    {
        $this->data['status_id'] = $model->id;
        return $this;
    }

    public function author(User $model): self
    {
        $this->data['user_id'] = $model->id;
        return $this;
    }

    public function executor(User $model): self
    {
        $this->data['executor_id'] = $model->id;
        return $this;
    }

    public function order(?Order $model): self
    {
        $this->data['order_id'] = $model->id ?? null;
        return $this;
    }

    public function created_at(CarbonImmutable $value): self
    {
        $this->data['created_at'] = $value;
        return $this;
    }
    public function due_date(CarbonImmutable $value): self
    {
        $this->data['due_date'] = $value;
        return $this;
    }
}
