<?php

namespace WezomCms\Cars\DTO;

use Illuminate\Database\Eloquent\Collection;
use WezomCms\Cars\Models\Model;

class ModelBrandListDto
{
    protected $collection;

    public function setCollection($collection)
    {
        if($collection instanceof Collection){
            $this->collection = $collection;
        }

        return $this;
    }

    /**
     * @return array
     * @throws \App\Exceptions\DtoException
     */
    public function toList()
    {
        $data = [];
        if(!$this->collection && $this->collection->isEmpty()){
            return $data;
        }

        foreach ($this->collection as $key => $model){
            /** @var $model Model */
            $data[$key]['ModelID'] = $model->niko_id;
            $data[$key]['ModelName'] = $model->name;
            $data[$key]['BrandId'] = $model->brand->niko_id;
            $data[$key]['BrandName'] = $model->brand->name;
        }

        return $data;
    }
}
