<?php

namespace Tests\Builders\Orders;

use App\Models\Order;
use Tests\Builders\BaseBuilder;

class WaypointNotesBuilder extends BaseBuilder
{

    public function modelClass(): string
    {
        return Order\WaypointNotes::class;
    }

    public function waypoint(Order\Waypoint $model): self
    {
        $this->data['waypoint_id'] = $model->id;
        return $this;
    }
}
