<?php

namespace WezomCms\Regions\DTO;

use WezomCms\Core\DTO\AbstractDto;
use WezomCms\Regions\Models\City;

class CityDto extends AbstractDto
{
    public function toArray()
    {
        if(!$this->model){
            return [];
        }

        $city = $this->model;

        /** @var $city City */
        return [
            'id' => $city->id,
            'name' => $city->name
        ];
    }
}

