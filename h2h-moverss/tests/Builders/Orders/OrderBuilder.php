<?php

namespace Tests\Builders\Orders;

use App\Models\Client;
use App\Models\Division;
use App\Models\Order;
use App\User;
use Carbon\CarbonImmutable;
use Tests\Builders\BaseBuilder;

class OrderBuilder extends BaseBuilder
{
    private array $tags = [];

    function modelClass(): string
    {
        return Order::class;
    }

    public function client(Client $model): self
    {
        $this->data['client_id'] = $model->id;
        return $this;
    }

    public function move_size($value): self
    {
        $this->data['move_size'] = $value;
        return $this;
    }

    public function sizing_is_auto(bool $value): self
    {
        $this->data['sizing_is_auto'] = $value;
        return $this;
    }

    public function manager(?User $model): self
    {
        $this->data['user_id'] = is_null($model) ? null : $model->id;
        return $this;
    }

    public function division(?Division $model): self
    {
        $this->data['division_id'] = $model->id ?? null;
        return $this;
    }

    public function status(Order\Status $model): self
    {
        $this->data['status_id'] = $model->id ?? null;
        return $this;
    }

    public function created_at(CarbonImmutable $value): self
    {
        $this->data['created_at'] = $value;
        return $this;
    }

    public function tags(Order\Tag ...$values): self
    {
        $this->tags = $values;
        return $this;
    }

    protected function afterSave($model): void
    {
        if(!empty($this->tags)) {
            $ids = [];
            foreach ($this->tags as $tag) {
                $ids[] = $tag->id;
            }

            $model->tags()->attach($ids);
        }
    }

    protected function afterClear(): void
    {
        $this->tags = [];
    }
}
