<?php

namespace WezomCms\Cars\DTO;

use Illuminate\Database\Eloquent\Collection;
use WezomCms\Cars\Models\EngineType;

class EngineTypeListFor1CDto
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
            /** @var $model EngineType */
            $data[$key]['EngineID'] = $model->id;
            $data[$key]['EngineName'] = $model->name;
        }

        return $data;
    }
}

