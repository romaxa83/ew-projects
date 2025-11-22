<?php

namespace App\Events\BS\Vehicles;

use App\Models\Vehicles\Vehicle;
use Illuminate\Queue\SerializesModels;

class DeleteVehicleEvent
{
    use SerializesModels;

    public Vehicle $vehicle;

    public function __construct(
        Vehicle $vehicle
    )
    {
        $this->vehicle = $vehicle;
    }
}
