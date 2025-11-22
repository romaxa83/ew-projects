<?php

namespace Tests\Builders\Orders\Dealer;

use App\Models\Orders\Dealer\Dimensions;
use App\Models\Orders\Dealer\PackingSlip;
use Tests\Builders\BaseBuilder;

class DimensionsBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return Dimensions::class;
    }

    public function setPackingSlip(PackingSlip $model): self
    {
        $this->data['packing_slip_id'] = $model->id;
        return $this;
    }
}
