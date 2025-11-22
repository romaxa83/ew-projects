<?php

namespace App\Foundations\Modules\History\Strategies\Details\Order\Parts;

use App\Foundations\Modules\History\Strategies\Details\BaseDetailsStrategy;
use App\Models\Customers\Customer;
use App\Models\Orders\Parts\Order;
use App\Models\Orders\Parts\Shipping;

class UpdateStrategy extends BaseDetailsStrategy
{
    public function __construct(
        protected Order $model,
        protected array $additional = [],
    )
    {}

    private function exclude(): array
    {
        return [
            'paid_amount',
            'debt_amount',
            'updated_at',
        ];
    }

    private function excludeMethod(): array
    {
        return [
            'order_id',
            'track_number',
            'created_at',
            'updated_at',
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
        try {
            if(!isset($this->additional['old_value'])){
                throw new \Exception('[Order\Parts\UpdateStrategy] you have to pass the values - "old_value"');
            }
            if(!isset($this->additional['change_fields'])){
                throw new \Exception('[Order\Parts\UpdateStrategy] you have to pass the values - "change_fields"');
            }

            $attr = $this->additional['change_fields'];
            $oldValue = $this->additional['old_value'];

            foreach ($this->exclude() as $key){
                unset($attr[$key]);
            }

            $tmp = [];

            if(isset($this->additional)){
                foreach ($attr as $k => $value){
                    if($value === null) continue;

                    if($k == 'customer_id'){
                        $customer = Customer::find($value);
                        $customerOld = Customer::find($oldValue[$k]);

                        $tmp[$k] = [
                            'old' => $customerOld->full_name,
                            'new' => $customer->full_name,
                            'type' => self::TYPE_UPDATED
                        ];
                    }  else {
                        if(in_array($k, $this->jsonFields())){
                            foreach (json_to_array($value) as $field => $v){
                                if(json_to_array($oldValue[$k])[$field] == $v) continue;

                                $tmp[$k.'.'.$field] = [
                                    'old' => json_to_array($oldValue[$k])[$field],
                                    'new' => $v,
                                    'type' => self::TYPE_UPDATED
                                ];
                            }
                        } else {
                            $tmp[$k] = [
                                'old' => $oldValue[$k],
                                'new' => $value,
                                'type' => self::TYPE_UPDATED
                            ];
                        }
                    }
                }
            }

//            if($oldValue['shipping_hash'] !== hash_data($this->model->shippingMethods)){
//                $oldShippingId = $oldValue['shipping']->pluck('id', 'id')->toArray();
//                foreach ($this->model->shippingMethods as $k => $method){
//                    $dataMethod = $this->excludeFields($method->getAttributes(), $this->excludeMethod());
//
////                    dd($dataMethod, $oldValue['shipping']);
//                    if($methodOld = $oldValue['shipping']->where('id', $dataMethod['id'])->first()){
//                        $dataMethodOld = $this->excludeFields($methodOld->getAttributes(), $this->excludeMethod());
//                        unset($oldShippingId[$dataMethod['id']]);
//
//                        if($dataMethod != $dataMethodOld){
//                            foreach ($dataMethod as $field => $value){
//                                if($dataMethod[$field] != $value){
//                                    $tmp["shipping_method.{$method->id}.$field"] = [
//                                        'old' => $dataMethodOld[$field],
//                                        'new' => $value,
//                                        'type' => self::TYPE_UPDATED
//                                    ];
//                                }
//                            }
//                        }
//                    } else {
//                        // это новые данные
//                        foreach ($dataMethod as $field => $value){
//                            if($field == 'id') continue;
//                            if($value === null) continue;
//                            $tmp["shipping_method.{$method->id}.$field"] = [
//                                'old' => null,
//                                'new' => $value,
//                                'type' => self::TYPE_ADDED
//                            ];
//                        }
//
//                    }
//
//                    // если не пустое, значит есть удаленные типы работ
//                    if(!empty($oldShippingId)){
//                        foreach ($oldShippingId as $id){
//                            /** @var $shippingOld Shipping */
//                            $shippingOld = $oldValue['shipping']->where('id', $id)->first();
//
//                            $dataShippingOld = $this->excludeFields($shippingOld->getAttributes(), $this->excludeMethod());
//                            foreach ($dataShippingOld as $field => $value){
//                                if($field == 'id') continue;
//                                if($dataShippingOld[$field] === null) continue;
//                                $tmp["shipping_method.{$shippingOld->id}.$field"] = [
//                                    'old' => $dataShippingOld[$field],
//                                    'new' => null,
//                                    'type' => self::TYPE_REMOVED
//                                ];
//                            }
//                        }
//                    }
//                }
//
//            }

            return $tmp;
        } catch (\Throwable $e){
            dd($e);
        }

    }

    private function excludeFields(array $data, array $exclude): array
    {
        foreach ($exclude as $key){
            unset($data[$key]);
        }

        return $data;
    }

    private function transformInventoryData($collect): array
    {
        $tmp = [];
        foreach ($collect as $i){
            $tmp[$i->inventory_id] = [
                'quantity' => $i->quantity,
                'price' => $i->price,
            ];
        }

        return $tmp;
    }
}
