<?php

namespace WezomCms\Cars\DTO;

use WezomCms\Cars\Models\EngineType;
use WezomCms\Core\DTO\AbstractDto;

class EngineTypeDto extends AbstractDto
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

        $model = $this->model;

        /** @var $model EngineType */
        return [
            'id' => $model->id,
            'name' => $model->name
        ];
    }
}
