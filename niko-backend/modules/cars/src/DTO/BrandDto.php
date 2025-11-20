<?php

namespace WezomCms\Cars\DTO;

use WezomCms\Cars\Models\Brand;
use WezomCms\Core\DTO\AbstractDto;

class BrandDto extends AbstractDto
{
    /**
     * @return array
     * @throws \App\Exceptions\DtoException
     */
    public function toArray()
    {
        if(!$this->model){
            return [];
        }

        $brand = $this->model;

        /** @var $brand Brand */
        return [
            'id' => $brand->id,
            'name' => $brand->name,
            'logoLink' => $brand->getImage()
        ];
    }
}

