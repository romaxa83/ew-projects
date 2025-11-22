<?php

namespace App\Filters\Dictionaries;

use App\Filters\BaseModelFilter;
use App\Models\Dictionaries\VehicleClass;
use App\Traits\Filter\ActiveFilterTrait;
use App\Traits\Filter\IdFilterTrait;
use App\Traits\Filter\SortFilterTrait;

class VehicleClassFilter extends BaseModelFilter
{
    use IdFilterTrait;
    use ActiveFilterTrait;
    use SortFilterTrait;

    public function vehicleForm(string $vehicleForm): void
    {
        $this->where('vehicle_form', $vehicleForm);
    }

    public function allowedOrders(): array
    {
        return VehicleClass::ALLOWED_SORTING_FIELDS;
    }
}
