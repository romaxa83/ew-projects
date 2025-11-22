<?php

namespace App\Filters\Dictionaries;

use App\Filters\BaseModelFilter;
use App\Models\Dictionaries\TireMake;
use App\Traits\Filter\ActiveFilterTrait;
use App\Traits\Filter\IdFilterTrait;
use App\Traits\Filter\ModeratedFilterTrait;
use App\Traits\Filter\SortFilterTrait;

class TireMakeFilter extends BaseModelFilter
{
    use IdFilterTrait;
    use ActiveFilterTrait;
    use SortFilterTrait;
    use ModeratedFilterTrait;

    public function allowedOrders(): array
    {
        return TireMake::ALLOWED_SORTING_FIELDS;
    }
}
