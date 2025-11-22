<?php

namespace Tests\Builders\Inventories;

use App\Contracts\Orders\Orderable;
use App\Enums\Inventories\Transaction\DescribeType;
use App\Enums\Inventories\Transaction\OperationType;
use App\Enums\Orders\OrderType;
use App\Models\Inventories\Inventory;
use App\Models\Inventories\Transaction;
use Carbon\CarbonImmutable;
use Tests\Builders\BaseBuilder;

class TransactionBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return Transaction::class;
    }

    public function inventory(Inventory $model): self
    {
        $this->data['inventory_id'] = $model->id;
        return $this;
    }

    public function order(Orderable $model): self
    {
        if($model->isPartsOrder()){
            $this->data['order_parts_id'] = $model->getId();
            $this->data['order_type'] = OrderType::Parts();
        } else {
            $this->data['order_id'] = $model->getId();
            $this->data['order_type'] = OrderType::BS();
        }

        return $this;
    }

    public function is_reserve(bool $value): self
    {
        $this->data['is_reserve'] = $value;
        return $this;
    }

    public function qty(float $value): self
    {
        $this->data['quantity'] = $value;
        return $this;
    }

    public function operation_type(OperationType $value): self
    {
        $this->data['operation_type'] = $value->value;
        return $this;
    }

    public function describe(DescribeType $value): self
    {
        $this->data['describe'] = $value->value;
        return $this;
    }

    public function transaction_date(CarbonImmutable $value): self
    {
        $this->data['transaction_date'] = $value;
        return $this;
    }

    public function price(float $value): self
    {
        $this->data['price'] = $value;
        return $this;
    }
}
