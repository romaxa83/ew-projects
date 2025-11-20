<?php

namespace WezomCms\Regions\DTO;

use WezomCms\Core\DTO\AbstractListDto;

class CityListDto extends AbstractListDto
{
    protected CityDto $cityDto;

    public function __construct()
    {
        $this->cityDto = resolve(CityDto::class);
    }

    /**
     * @return array
     * @throws \App\Exceptions\DtoException
     */
    public function toList()
    {

        $this->data['cities'] = [];
        if(!$this->collection && $this->collection->isEmpty()){
            return $this->data;
        }

        foreach ($this->collection as $key => $model){
            $this->data['cities'][$key] = $this->cityDto->setModel($model)->toArray();
        }

        return $this->data;
    }
}
