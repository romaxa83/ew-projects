<?php

namespace WezomCms\Services\DTO;

use WezomCms\Core\DTO\AbstractListDto;

class StoServiceListDto extends AbstractListDto
{
    protected StoServiceDto $serviceDto;

    public function __construct()
    {
        $this->serviceDto = resolve(StoServiceDto::class);
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
            $this->data[$key] = $this->serviceDto->setModel($model)->toArray();
        }

        return $this->data;
    }
}
