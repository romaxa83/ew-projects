<?php

namespace WezomCms\Dealerships\DTO;

use Illuminate\Database\Eloquent\Collection;
use WezomCms\Dealerships\Models\Dealership;

class DealershipListFor1CDto
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
            /** @var $model Dealership */
            $data[$key]['DealerID'] = $model->id;
            $data[$key]['DealerName'] = $model->name;
            $data[$key]['DealerBrandName'] = $model->brand->name ?? null;
        }

        return $data;
    }
}
