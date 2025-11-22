<?php

namespace App\Foundations\Modules\History\Strategies\Details\Inventory;

use App\Foundations\Modules\History\Strategies\Details\BaseDetailsStrategy;
use App\Models\Inventories\Inventory;

class QuantityDecreasedStrategy extends BaseDetailsStrategy
{
    public function __construct(
        protected Inventory $model,
        protected array $additional = [],
    )
    {}

    private function excludeTransaction(): array
    {
        return [
            'id',
            'inventory_id',
            'order_id',
            'invoice_number',
            'updated_at',
            'created_at'
        ];
    }

    public function getDetails(): array
    {
        $attr = $this->model->getChanges();

        $transaction = $this->model->transactions()->where('id', $this->additional['transaction_id'])->first();
        $transactionAttr = $transaction->getAttributes();
        foreach ($this->excludeTransaction() as $key){
            unset($transactionAttr[$key]);
        }

        $tmp = [];
        foreach ($transactionAttr as $k => $value){
            if($value === null) continue;
            if($k == 'quantity') {
                $tmp[$k] = [
                    'old' => $this->additional['quantity'],
                    'new' => $attr['quantity'],
                    'type' => self::TYPE_UPDATED
                ];
            } else {
                $tmp[$k] = [
                    'old' => null,
                    'new' => $value,
                    'type' => self::TYPE_ADDED
                ];
            }
        }

        return $tmp;
    }
}
