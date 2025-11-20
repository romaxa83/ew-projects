<?php

namespace WezomCms\Users\DTO;

use WezomCms\Cars\DTO\BrandDto;
use WezomCms\Cars\DTO\EngineTypeDto;
use WezomCms\Cars\DTO\ModelDto;
use WezomCms\Cars\DTO\TransmissionDto;
use WezomCms\Users\Models\Car;
use WezomCms\Core\DTO\AbstractDto;

class CarDtoFromData
{
    protected $data;

    protected BrandDto $brandDto;
    protected ModelDto $modelDto;
    protected TransmissionDto $transmissionDto;
    protected EngineTypeDto $engineTypeDto;

    public function __construct()
    {
        $this->brandDto = resolve(BrandDto::class);
        $this->modelDto = resolve(ModelDto::class);
        $this->transmissionDto = resolve(TransmissionDto::class);
        $this->engineTypeDto = resolve(EngineTypeDto::class);
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @return array
     * @throws \App\Exceptions\DtoException
     */
    public function toArray()
    {
        dd($this->data);

        if(!$this->data){
            return [];
        }

//        $car = $this->model;

        /** @var $car Car */
        return [
//            'id' => $car->id,
//            'vinCode' => $car->vin_code,
//            'number' => $car->number,
//            'year' => $car->year,
//            'isFamilyCar' => $car->is_family_car,
//            'isOnModeration' => $car->is_verify,
//            'dealerCenterId' => $car->dealership_id,
//            'brand' => $this->brandDto->setModel($car->brand)->toArray(),
//            'model' => $this->modelDto->setModel($car->model)->toArray(),
//            'transmission' => $this->transmissionDto->setModel($car->transmission)->toArray(),
//            'engine' => $this->engineTypeDto->setModel($car->engineType)->toArray(),
//            'engineVolume' => $car->engine_volume
        ];
    }
}
