<?php

namespace WezomCms\Services\DTO;

use WezomCms\Core\DTO\AbstractDto;
use WezomCms\Services\Models\Service;

class InsuranceDto extends AbstractDto
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

        $service = $this->model;

        /** @var $service Service */
        return [
            'id' => $service->id,
            'name' => $service->name
        ];
    }
}
