<?php

namespace WezomCms\Cars\DTO;

use Illuminate\Database\Eloquent\Collection;
use WezomCms\Cars\Models\EngineType;
use WezomCms\Cars\Models\Transmission;

class TransmissionListFor1CDto
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
            /** @var $model Transmission */
            $data[$key]['TransmissionID'] = $model->id;
            $data[$key]['TransmissionName'] = $model->name;
        }

        return $data;
    }
}
