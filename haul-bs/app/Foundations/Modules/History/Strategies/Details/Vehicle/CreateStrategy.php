<?php

namespace App\Foundations\Modules\History\Strategies\Details\Vehicle;

use App\Enums\Vehicles\VehicleType;
use App\Foundations\Modules\History\Strategies\Details\BaseDetailsStrategy;
use App\Models\Customers\Customer;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Truck;

class CreateStrategy extends BaseDetailsStrategy
{
    public function __construct(protected Truck|Trailer $model)
    {}

    private function exclude(): array
    {
        $fields = [
            'id',
            'temporary_plate',
            'updated_at',
            'created_at'
        ];

        if($this->model->isTrailer()){
            $fields[] = 'type';
        }

        return $fields;
    }

    public function getDetails(): array
    {
        $attr = $this->model->getAttributes();

        foreach ($this->exclude() as $key){
            unset($attr[$key]);
        }

        $tmp = [];
        foreach ($attr as $k => $value){
            if($k == 'type'){
                $tmp[$k] = [
                    'old' => null,
                    'new' => VehicleType::name($value),
                    'type' => self::TYPE_ADDED
                ];
            } elseif ($k == 'customer_id'){
                $customer = Customer::find($value);
                $tmp[$k] = [
                    'old' => null,
                    'new' => $customer->full_name,
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
        if($this->model->tags->isNotEmpty()){
            $tmp['tags'] = [
                'old' => null,
                'new' => $this->model->tags->getNamesAsString(),
                'type' => self::TYPE_ADDED
            ];
        }
        if(!empty($this->model->getAttachments())){
            foreach ($this->model->getAttachments() as $media) {
                /** @var $media \Spatie\MediaLibrary\MediaCollections\Models\Media */
                $tmp["{$media->collection_name}.{$media->id}.name"] = [
                    'new' => $media->name,
                    'old' => null,
                    'type' => self::TYPE_ADDED
                ];
            }
        }

        return $tmp;
    }
}
