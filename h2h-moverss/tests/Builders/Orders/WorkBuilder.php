<?php

namespace Tests\Builders\Orders;

use App\Models\Order;
use App\Models\WorkTypes;
use Tests\Builders\BaseBuilder;

class WorkBuilder extends BaseBuilder
{
    private array $types = [];

    public function modelClass(): string
    {
        return Order\Work::class;
    }

    public function order(Order $model): self
    {
        $this->data['order_id'] = $model->id;
        return $this;
    }

    public function start_date(string $value): self
    {
        $this->data['start_date'] = $value;
        return $this;
    }

    public function types(WorkTypes ...$values): self
    {
        $this->types = $values;
        return $this;
    }

    protected function afterSave($model): void
    {
        /** @var $model Order\Work */
        if(!empty($this->types)) {
            $ids = [];
            foreach ($this->types as $type) {
                $ids[] = $type->id;
            }

            $model->workTypes()->attach($ids);
        }
    }

    protected function afterClear(): void
    {
        $this->tags = [];
    }
}
