<?php

use WezomCms\Users\Models\Car;

class CarAfterSaveDto
{
    /**
     * @return array
     * @throws \App\Exceptions\DtoException
     */
    public function toArray(array $data)
    {

//        return [
//            'vinCode' => $data['vinCode'],
//            'number' => $data['number'],
//            'year' => $data['year'],
//            'milleage' => $data['milleage'],
//            'milleage' => $car->millage,
//            'isFamilyCar' => $car->is_family_car,
//            'isOnModeration' => $car->is_verify,
//            'dealerCenterId' => $car->dealership_id,
//            'brandId' => $this->brandDto->setModel($car->brand)->toArray(),
//            'modelId' => $this->modelDto->setModel($car->model)->toArray(),
//            'transmissionId' => $this->transmissionDto->setModel($car->transmission)->toArray(),
//            'engineDescription' => $car->engine_description
//        ];
    }
}
