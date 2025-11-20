<?php

namespace WezomCms\Users\DTO;

use Illuminate\Database\Eloquent\Collection;
use WezomCms\Users\Models\LoyaltyLevel as LoyaltyLevelModel;
use WezomCms\Users\Types\LoyaltyLevel;

class LoyaltyLevelListDto
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
            /** @var $model LoyaltyLevelModel */
            $data[$key]['level'] = $model->level;
            $data[$key]['name'] = LoyaltyLevel::getName($model->level);
            $data[$key]['spending'] = $model->getSumServices();
            $data[$key]['autos'] = $model->count_auto;
            $data[$key]['discountService'] = $model->getDiscountSto();
            $data[$key]['discountSpare'] = $model->getDiscountSpares();
        }

        return $data;
    }
}
