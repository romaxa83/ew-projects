<?php


namespace App\Filters\Locations;


use App\Filters\BaseModelFilter;
use App\Models\Locations\Region;
use App\Traits\Filter\SortFilterTrait;

class RegionFilter extends BaseModelFilter
{
    use SortFilterTrait;

    protected function allowedTranslateOrders(): array
    {
        return Region::ALLOWED_TRANSLATE_FIELDS;
    }
}
