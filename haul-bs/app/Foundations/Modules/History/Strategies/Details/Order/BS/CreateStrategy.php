<?php

namespace App\Foundations\Modules\History\Strategies\Details\Order\BS;

use App\Foundations\Modules\History\Strategies\Details\BaseDetailsStrategy;
use App\Models\Orders\BS\Order;
use App\Models\Orders\BS\TypeOfWorkInventory;
use App\Models\Users\User;
use Carbon\CarbonImmutable;

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

    public function getDetails(): array
    {
        $attr = $this->model->getAttributes();

        foreach ($this->exclude() as $key){
            unset($attr[$key]);
        }

        $tmp = [];
        foreach ($attr as $k => $value){
            if($value === null) continue;

            if($k == 'mechanic_id'){
                $user = User::find($value);
                $tmp[$k] = [
                    'old' => null,
                    'new' => $user->full_name,
                    'type' => self::TYPE_ADDED
                ];
            } elseif ($k == 'implementation_date'){

                $date = CarbonImmutable::createFromFormat('Y-m-d H:i:s', $value);
                $tmp[$k] = [
                    'old' => null,
                    'new' => $date->format('Y-m-d H:i'),
                    'type' => self::TYPE_ADDED
                ];
            } elseif ($k == 'due_date'){

                $date = CarbonImmutable::createFromFormat('Y-m-d H:i:s', $value);
                $tmp[$k] = [
                    'old' => null,
                    'new' => $date->format('Y-m-d'),
                    'type' => self::TYPE_ADDED
                ];
            } else {
                $tmp[$k] = [
                    'old' => null,
                    'new' => $value,
                    'type' => self::TYPE_ADDED
                ];
            }
        }

        if(!empty($this->model->getAttachments())){
            foreach ($this->model->getAttachments() as $media) {
                /** @var $media \Spatie\MediaLibrary\MediaCollections\Models\Media */
                $tmp["{$media->collection_name}.{$media->id}.name"] = [
                    'old' => null,
                    'new' => $media->name,
                    'type' => self::TYPE_ADDED
                ];
            }
        }

        foreach ($this->model->typesOfWork as $work){
            $tmp["typesOfWork.{$work->id}.name"] = [
                'old' => null,
                'new' => $work->name,
                'type' => self::TYPE_ADDED
            ];
            $tmp["typesOfWork.{$work->id}.duration"] = [
                'old' => null,
                'new' => $work->duration,
                'type' => self::TYPE_ADDED
            ];
            $tmp["typesOfWork.{$work->id}.hourly_rate"] = [
                'old' => null,
                'new' => $work->hourly_rate,
                'type' => self::TYPE_ADDED
            ];

            foreach ($work->inventories as $workInventory){
                /** @var $workInventory TypeOfWorkInventory */
                $tmp["typesOfWork.{$work->id}.inventories.{$workInventory->inventory_id}.name"] = [
                    'old' => null,
                    'new' => $workInventory->inventory->name,
                    'type' => self::TYPE_ADDED
                ];
                $tmp["typesOfWork.{$work->id}.inventories.{$workInventory->inventory_id}.price"] = [
                    'old' => null,
                    'new' => $workInventory->price,
                    'type' => self::TYPE_ADDED
                ];
                $tmp["typesOfWork.{$work->id}.inventories.{$workInventory->inventory_id}.quantity"] = [
                    'old' => null,
                    'new' => $workInventory->quantity,
                    'type' => self::TYPE_ADDED
                ];
            }
        }

        return $tmp;
    }
}
