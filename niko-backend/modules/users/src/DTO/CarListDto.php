<?php

namespace WezomCms\Users\DTO;

use WezomCms\Core\DTO\AbstractListDto;

class CarListDto extends AbstractListDto
{
    protected CarDto $carDto;

    public function __construct()
    {
        $this->carDto = resolve(CarDto::class);
    }

    /**
     * @return array
     * @throws \App\Exceptions\DtoException
     */
    public function toList()
    {
        if(!$this->collection && $this->collection->isEmpty()){
            return $this->data;
        }

        foreach ($this->collection as $key => $model){
            $this->data[$key] = $this->carDto->setModel($model)->toArray();
        }

        return $this->data;
    }
}
