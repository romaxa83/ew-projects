<?php

namespace WezomCms\Services\DTO;

use WezomCms\Core\DTO\AbstractListDto;

class InsuranceListDto extends AbstractListDto
{
    protected InsuranceDto $serviceDto;

    public function __construct()
    {
        $this->serviceDto = resolve(InsuranceDto::class);
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
