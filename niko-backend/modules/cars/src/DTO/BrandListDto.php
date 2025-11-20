<?php

namespace WezomCms\Cars\DTO;

use WezomCms\Core\DTO\AbstractListDto;

class BrandListDto extends AbstractListDto
{
    protected BrandDto $brandDto;

    public function __construct()
    {
        $this->brandDto = resolve(BrandDto::class);
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
//            dd($model);
            $this->data[$key] = $this->brandDto->setModel($model)->toArray();
        }

        return $this->data;
    }
}
