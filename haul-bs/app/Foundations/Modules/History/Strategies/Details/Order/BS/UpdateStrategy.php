<?php

namespace App\Foundations\Modules\History\Strategies\Details\Order\BS;

use App\Foundations\Modules\History\Strategies\Details\BaseDetailsStrategy;
use App\Models\Orders\BS\Order;
use App\Models\Orders\BS\TypeOfWork;
use App\Models\Users\User;
use Carbon\CarbonImmutable;

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
        ];
    }

    private function excludeWork(): array
    {
        return [
            'order_id',
            'created_at',
            'updated_at',
        ];
    }

    public function getDetails(): array
    {
        try {
            if(!isset($this->additional['old_value'])){
                throw new \Exception('[Order\BS\UpdateStrategy] you have to pass the values - "old_value"');
            }
            if(!isset($this->additional['change_fields'])){
                throw new \Exception('[Order\BS\UpdateStrategy] you have to pass the values - "change_fields"');
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

                    if($k == 'mechanic_id'){
                        $user = User::find($value);
                        $userOld = User::find($oldValue[$k]);

                        $tmp[$k] = [
                            'old' => $userOld->full_name,
                            'new' => $user->full_name,
                            'type' => self::TYPE_UPDATED
                        ];
                    } elseif ($k == 'implementation_date'){
                        $date = CarbonImmutable::createFromFormat('Y-m-d H:i:s', $value);
                        $dateOld = CarbonImmutable::createFromFormat('Y-m-d H:i:s', $oldValue[$k]);
                        $tmp[$k] = [
                            'old' => $dateOld->format('Y-m-d H:i'),
                            'new' => $date->format('Y-m-d H:i'),
                            'type' => self::TYPE_UPDATED
                        ];
                    } elseif ($k == 'due_date'){
                        $date = CarbonImmutable::createFromFormat('Y-m-d H:i:s', $value);
                        $dateOld = CarbonImmutable::createFromFormat('Y-m-d H:i:s', $oldValue[$k]);
                        $tmp[$k] = [
                            'old' => $dateOld->format('Y-m-d'),
                            'new' => $date->format('Y-m-d'),
                            'type' => self::TYPE_UPDATED
                        ];
                    } else {
                        $tmp[$k] = [
                            'old' => $oldValue[$k],
                            'new' => $value,
                            'type' => self::TYPE_UPDATED
                        ];
                    }
                }

                foreach ($this->model->media as $media){
                    /** @var $media \Spatie\MediaLibrary\MediaCollections\Models\Media */
                    if(
                        isset($oldValue['media'])
                        && (
                            $oldValue['media']->isEmpty()
                            || $oldValue['media']->contains(fn($i) => $i->id != $media->id)
                        )
                    ){
                        $tmp["{$media->collection_name}.{$media->id}.name"] = [
                            'old' => null,
                            'new' => $media->name,
                            'type' => self::TYPE_ADDED
                        ];
                    }
                }

                // проверяем есть ли изменения в типах работ
                if($oldValue['type_of_work_hash'] !== hash_data($this->model->typesOfWork)){
                    $oldWorkId = $oldValue['type_of_work']->pluck('id', 'id')->toArray();
                    foreach ($this->model->typesOfWork as $work){
                        $dataWork = $this->excludeFields($work->getAttributes(), $this->excludeWork());

                        // если есть модель, значит проверяем на изменения
                        if($workOld = $oldValue['type_of_work']->where('id', $dataWork['id'])->first()){

                            $dataWorkOld = $this->excludeFields($workOld->getAttributes(), $this->excludeWork());
                            unset($oldWorkId[$dataWork['id']]);
                            // если данные не равны значит есть изменения

                            if($dataWork != $dataWorkOld){
                                foreach ($dataWork as $field => $value){
                                    if($dataWorkOld[$field] != $value){
                                        $tmp["typesOfWork.{$work->id}.$field"] = [
                                            'old' => $dataWorkOld[$field],
                                            'new' => $value,
                                            'type' => self::TYPE_UPDATED
                                        ];
                                    }
                                }
                            }

                            $oldInv = $this->transformInventoryData($workOld->inventories);
                            $inv = $this->transformInventoryData($work->inventories);

                            if($inv !== $oldInv){
                                // это удаленные товары из работ
                                if(empty($inv) && !empty($oldInv)){
                                    foreach ($oldInv as $i => $v){
                                        $tmp["typesOfWork.{$work->id}.inventories.{$i}.quantity"] = [
                                            'old' => $v['quantity'],
                                            'new' => null,
                                            'type' => self::TYPE_REMOVED
                                        ];
                                        $tmp["typesOfWork.{$work->id}.inventories.{$i}.price"] = [
                                            'old' => $v['price'],
                                            'new' => null,
                                            'type' => self::TYPE_REMOVED
                                        ];
                                    }
                                }

                                foreach ($inv as $id => $values){
                                    if (isset($oldInv[$id])){
                                        if ($oldInv[$id]['quantity'] !== $values['quantity']) {
                                            $tmp["typesOfWork.{$work->id}.inventories.{$id}.quantity"] = [
                                                'old' => $oldInv[$id]['quantity'],
                                                'new' => $values['quantity'],
                                                'type' => self::TYPE_UPDATED
                                            ];
                                        }
                                        if ($oldInv[$id]['price'] !== $values['price']) {
                                            $tmp["typesOfWork.{$work->id}.inventories.{$id}.price"] = [
                                                'old' => $oldInv[$id]['price'],
                                                'new' => $values['price'],
                                                'type' => self::TYPE_UPDATED
                                            ];
                                        }

                                        unset($oldInv[$id]);
                                    } else {
                                        $tmp["typesOfWork.{$work->id}.inventories.{$id}.quantity"] = [
                                            'old' => null,
                                            'new' => $values['quantity'],
                                            'type' => self::TYPE_ADDED
                                        ];
                                        $tmp["typesOfWork.{$work->id}.inventories.{$id}.price"] = [
                                            'old' => null,
                                            'new' => $values['price'],
                                            'type' => self::TYPE_ADDED
                                        ];
                                    }

                                    if(!empty($oldInv)){
                                        foreach ($oldInv as $i => $v){
                                            $tmp["typesOfWork.{$work->id}.inventories.{$i}.quantity"] = [
                                                'old' => $v['quantity'],
                                                'new' => null,
                                                'type' => self::TYPE_REMOVED
                                            ];
                                            $tmp["typesOfWork.{$work->id}.inventories.{$i}.price"] = [
                                                'old' => $v['price'],
                                                'new' => null,
                                                'type' => self::TYPE_REMOVED
                                            ];
                                        }
                                    }
                                }
                            }

                        } else {
                            // это новые данные
                            foreach ($dataWork as $field => $value){
                                if($field == 'id') continue;
                                $tmp["typesOfWork.{$work->id}.$field"] = [
                                    'old' => null,
                                    'new' => $value,
                                    'type' => self::TYPE_ADDED
                                ];

                                foreach ($work->inventories as $inventory){
                                    $tmp["typesOfWork.{$work->id}.inventories.{$inventory->inventory_id}.quantity"] = [
                                        'old' => null,
                                        'new' => $inventory->quantity,
                                        'type' => self::TYPE_ADDED
                                    ];
                                    $tmp["typesOfWork.{$work->id}.inventories.{$inventory->inventory_id}.price"] = [
                                        'old' => null,
                                        'new' => $inventory->price,
                                        'type' => self::TYPE_ADDED
                                    ];
                                }
                            }
                        }
                    }
                    // если не пустое, значит есть удаленные типы работ
                    if(!empty($oldWorkId)){
                        foreach ($oldWorkId as $id){
                            /** @var $workOld TypeOfWork */
                            $workOld = $oldValue['type_of_work']->where('id', $id)->first();

                            $dataWorkOld = $this->excludeFields($workOld->getAttributes(), $this->excludeWork());
                            foreach ($dataWorkOld as $field => $value){
                                if($field == 'id') continue;
                                $tmp["typesOfWork.{$workOld->id}.$field"] = [
                                    'old' => $dataWorkOld[$field],
                                    'new' => null,
                                    'type' => self::TYPE_REMOVED
                                ];
                            }
                            foreach ($workOld->inventories as $inv){
                                $tmp["typesOfWork.{$workOld->id}.inventories.{$inv->inventory_id}.quantity"] = [
                                    'old' => $inv->quantity,
                                    'new' => null,
                                    'type' => self::TYPE_REMOVED
                                ];
                                $tmp["typesOfWork.{$workOld->id}.inventories.{$inv->inventory_id}.price"] = [
                                    'old' => $inv->inventory->price_retail,
                                    'new' => null,
                                    'type' => self::TYPE_REMOVED
                                ];
                            }
                        }
                    }
                }
            }

            return $tmp;
        } catch (\Throwable $e){
//            dd($e);
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
