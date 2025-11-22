<?php

namespace App\Filters\Dictionaries;

use App\Filters\BaseModelFilter;
use App\Models\Dictionaries\VehicleType;
use App\Traits\Filter\ActiveFilterTrait;
use App\Traits\Filter\IdFilterTrait;
use App\Traits\Filter\SortFilterTrait;
use Illuminate\Database\Eloquent\Builder;

class VehicleTypeFilter extends BaseModelFilter
{
    use IdFilterTrait;
    use ActiveFilterTrait;
    use SortFilterTrait;

    public function vehicleClass(int $vehicleClassId): void
    {
        $this->whereHas(
            'vehicleClasses',
            fn (Builder|VehicleType $b) => $b->where('vehicle_class_id', $vehicleClassId)
        );
    }

    public function allowedOrders(): array
    {
        return VehicleType::ALLOWED_SORTING_FIELDS;
    }
}
