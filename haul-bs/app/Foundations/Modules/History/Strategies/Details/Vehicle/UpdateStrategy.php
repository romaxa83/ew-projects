<?php

namespace App\Foundations\Modules\History\Strategies\Details\Vehicle;

use App\Enums\Vehicles\VehicleType;
use App\Foundations\Modules\History\Strategies\Details\BaseDetailsStrategy;
use App\Models\Customers\Customer;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Truck;

class UpdateStrategy extends BaseDetailsStrategy
{
    public function __construct(
        protected Truck|Trailer $model,
        protected array $additional = [],
    )
    {}

    private function exclude(): array
    {
        $fields = [
            'updated_at',
        ];

        if($this->model->isTrailer()){
            $fields[] = 'type';
        }

        return $fields;
    }

    public function getDetails(): array
    {
        $attr = $this->model->getChanges();

        foreach ($this->exclude() as $key){
            unset($attr[$key]);
        }

        $tmp = [];

        if(isset($this->additional)){
            foreach ($attr as $k => $value){
                if($k == 'type'){
                    $tmp[$k] = [
                        'old' => VehicleType::name($this->additional[$k]),
                        'new' => VehicleType::name($value),
                        'type' => self::TYPE_UPDATED
                    ];
                } elseif ($k == 'customer_id'){
                    $customer = Customer::find($value);
                    $customerOld = Customer::find($this->additional[$k]);
                    $tmp[$k] = [
                        'old' => $customerOld->full_name,
                        'new' => $customer->full_name,
                        'type' => self::TYPE_UPDATED
                    ];
                } else {
                    $tmp[$k] = [
                        'old' => $this->additional[$k],
                        'new' => $value,
                        'type' => self::TYPE_UPDATED
                    ];
                }
            }
            if(
                isset($this->additional['tags'])
                && $this->additional['tags'] != $this->model->tags->getNamesAsString()
            ){
                $tmp['tags'] = [
                    'old' => $this->additional['tags'],
                    'new' => $this->model->tags->getNamesAsString(),
                    'type' => self::TYPE_UPDATED
                ];
            }
            if(!empty($this->model->getAttachments())){
                foreach ($this->model->getAttachments() as $media){
                    /** @var $media \Spatie\MediaLibrary\MediaCollections\Models\Media */
                    if(
                        isset($this->additional['media'])
                        && $this->additional['media']->contains(fn($i) => $i->id != $media->id)
                    ){
                        $tmp["{$media->collection_name}.{$media->id}.name"] = [
                            'old' => null,
                            'new' => $media->name,
                            'type' => self::TYPE_ADDED
                        ];
                    }
                }
            }
        }

        return $tmp;
    }
}
