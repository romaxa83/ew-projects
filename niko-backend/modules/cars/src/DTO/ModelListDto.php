<?php

namespace WezomCms\Cars\DTO;

use WezomCms\Core\DTO\AbstractListDto;

class ModelListDto extends AbstractListDto
{
    protected ModelDto $modelDto;

    public function __construct()
    {
        $this->modelDto = resolve(ModelDto::class);
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
            $this->data[$key] = $this->modelDto->setModel($model)->toArray();
        }

        return $this->data;
    }
}
