<?php

namespace Tests\Builders\Trucks;

use App\Models\DispatchTruck;
use App\Models\Order\Work;
use App\Models\Truck\Truck;
use Tests\Builders\BaseBuilder;

class DispatchTruckBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return DispatchTruck::class;
    }

    public function truck(Truck $model): self
    {
        $this->data['truck_id'] = $model->id;
        return $this;
    }

    public function work(Work $model): self
    {
        $this->data['work_id'] = $model->id;
        return $this;
    }
}

