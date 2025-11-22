<?php

namespace App\Foundations\Modules\History\Strategies\Details\Order\Parts;

use App\Foundations\Modules\History\Strategies\Details\BaseDetailsStrategy;
use App\Models\Customers\Customer;
use App\Models\Orders\Parts\Order;
use App\Models\Users\User;

class CreateStrategy extends BaseDetailsStrategy
{
    public function __construct(protected Order $model)
    {}

    private function exclude(): array
    {
        return [
            'id',
            'status_changed_at',
            'is_billed',
            'is_paid',
            'paid_amount',
            'debt_amount',
            'deleted_at',
            'updated_at',
            'created_at'
        ];
    }

    protected function jsonFields(): array
    {
        return [
            'billing_address',
            'delivery_address',
        ];
    }

    public function getDetails(): array
    {
        $attr = $this->model->getAttributes();

        $model = $this->model->load(['items.inventory']);

        foreach ($this->exclude() as $key){
            unset($attr[$key]);
        }

        $tmp = [];
        foreach ($attr as $k => $value){
            if($value === null) continue;
            if($k == 'customer_id'){
                $user = Customer::find($value);
                $tmp[$k] = [
                    'old' => null,
                    'new' => $user->full_name,
                    'type' => self::TYPE_ADDED
                ];
            } elseif ($k == 'sales_manager_id') {
                $user = User::find($value);
                $tmp[$k] = [
                    'old' => null,
                    'new' => $user->full_name,
                    'type' => self::TYPE_ADDED
                ];
            } else {
                if(in_array($k, $this->jsonFields())){
                    foreach (json_to_array($value) as $field => $v){
                        $tmp[$k.'.'.$field] = [
                            'old' => null,
                            'new' => $v,
                            'type' => self::TYPE_ADDED
                        ];
                    }
                } else {
                    $tmp[$k] = [
                        'old' => null,
                        'new' => $value,
                        'type' => self::TYPE_ADDED
                    ];
                }
            }
        }

        foreach ($model->items as $item){
            $tmp["items.{$item->id}.inventories.{$item->inventory_id}.name"] = [
                'old' => null,
                'new' => $item->inventory->name,
                'type' => self::TYPE_ADDED
            ];
            $tmp["items.{$item->id}.inventories.{$item->inventory_id}.quantity"] = [
                'old' => null,
                'new' => $item->qty,
                'type' => self::TYPE_ADDED
            ];
        }

        return $tmp;
    }
}
