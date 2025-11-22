<?php

namespace App\Filters\Dictionaries;

use App\Filters\BaseModelFilter;
use App\Models\Dictionaries\VehicleModel;
use App\Traits\Filter\ActiveFilterTrait;
use App\Traits\Filter\IdFilterTrait;
use App\Traits\Filter\LikeRawFilterTrait;
use App\Traits\Filter\ModeratedFilterTrait;
use App\Traits\Filter\SortFilterTrait;

class VehicleModelFilter extends BaseModelFilter
{
    use IdFilterTrait;
    use ActiveFilterTrait;
    use LikeRawFilterTrait;
    use SortFilterTrait;
    use ModeratedFilterTrait;

    public function query(string $query): void
    {
        $this->likeRaw('title', $query);
    }

    public function vehicleMake(int $vehicleMakeId): void
    {
        $this->where('vehicle_make_id', $vehicleMakeId);
    }

    public function allowedOrders(): array
    {
        return VehicleModel::ALLOWED_SORTING_FIELDS;
    }
}
