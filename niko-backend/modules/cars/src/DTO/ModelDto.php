<?php

namespace WezomCms\Cars\DTO;

use WezomCms\Cars\Models\Model;
use WezomCms\Core\DTO\AbstractDto;

class ModelDto extends AbstractDto
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

        /** @var $model Model */
        return [
            'id' => $model->id,
            'name' => $model->name
        ];
    }
}
