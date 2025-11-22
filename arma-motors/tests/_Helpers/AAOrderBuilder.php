<?php

namespace Tests\_Helpers;

use App\Models\AA\AAOrder;
use App\Models\AA\AAOrderPlanning;

class AAOrderBuilder
{
    private $planningData = [];
    private $data = [];

    public function setData($value): self
    {
        $this->data = $value;
        return $this;
    }

    public function setPlanningData($value): self
    {
        $this->planningData = $value;
        return $this;
    }

    public function create()
    {
        $model = $this->save();

        if(!empty($this->planningData)){

            foreach ($this->planningData as $item){
                $item['aa_order_id'] = $model->id;
                AAOrderPlanning::factory()->create($item);
            }
        }

        return $model;
    }

    private function save()
    {
        return AAOrder::factory()->create($this->data);
    }
}
