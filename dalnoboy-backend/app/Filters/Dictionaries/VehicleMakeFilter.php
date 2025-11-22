<?php

namespace App\Filters\Dictionaries;

use App\Filters\BaseModelFilter;
use App\Models\Dictionaries\VehicleMake;
use App\Traits\Filter\ActiveFilterTrait;
use App\Traits\Filter\IdFilterTrait;
use App\Traits\Filter\LikeRawFilterTrait;
use App\Traits\Filter\ModeratedFilterTrait;
use App\Traits\Filter\SortFilterTrait;

class VehicleMakeFilter extends BaseModelFilter
{
    use IdFilterTrait;
    use ActiveFilterTrait;
    use SortFilterTrait;
    use ModeratedFilterTrait;
    use LikeRawFilterTrait;

    public function allowedOrders(): array
    {
        return VehicleMake::ALLOWED_SORTING_FIELDS;
    }

    public function query(string $query): void
    {
        $this->likeRaw('title', $query);
    }

    public function vehicleForm(string $vehicleForm): void
    {
        $this->where('vehicle_form', $vehicleForm);
    }
}
