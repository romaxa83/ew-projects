<?php


namespace App\Filters\Vehicles\Schemas;


use App\Filters\BaseModelFilter;
use App\Traits\Filter\IdFilterTrait;
use App\Traits\Filter\LikeRawFilterTrait;

class SchemaVehicleFilter extends BaseModelFilter
{
    use IdFilterTrait;
    use LikeRawFilterTrait;

    public function name(string $name): void
    {
        $this->likeRaw('name', $name);
    }

    public function vehicleForm(string $vehicleForm): void
    {
        $this->where('vehicle_form', $vehicleForm);
    }
}
