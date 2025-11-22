<?php

namespace App\Filters\Dictionaries;

use App\Filters\BaseModelFilter;
use App\Models\Dictionaries\TireType;
use App\Traits\Filter\ActiveFilterTrait;
use App\Traits\Filter\IdFilterTrait;
use App\Traits\Filter\SortFilterTrait;

class TireRelationshipTypeFilter extends BaseModelFilter
{
    use IdFilterTrait;
    use ActiveFilterTrait;
    use SortFilterTrait;

    public function allowedOrders(): array
    {
        return TireType::ALLOWED_SORTING_FIELDS;
    }
}
