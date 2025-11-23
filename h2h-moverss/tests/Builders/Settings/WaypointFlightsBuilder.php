<?php

namespace Tests\Builders\Settings;

use App\Models\Settings\WaypointFlights;
use Tests\Builders\BaseBuilder;

class WaypointFlightsBuilder extends BaseBuilder
{
    public function modelClass(): string
    {
        return WaypointFlights::class;
    }
}
