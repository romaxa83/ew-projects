<?php

namespace WezomCms\Cars\DTO;

use WezomCms\Cars\Models\Model;
use WezomCms\Cars\Models\Transmission;
use WezomCms\Core\DTO\AbstractDto;

class TransmissionDto extends AbstractDto
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

        /** @var $model Transmission */
        return [
            'id' => $model->id,
            'name' => $model->name
        ];
    }
}
