<?php

namespace Tests\Builders\Trucks;

use App\Models\Partners\Partner;
use App\Models\Truck\Truck;
use Tests\Builders\BaseBuilder;

class TruckBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return Truck::class;
    }

    public function partner(Partner $model): self
    {
        $this->data['partner_id'] = $model->id;
        return $this;
    }
}
