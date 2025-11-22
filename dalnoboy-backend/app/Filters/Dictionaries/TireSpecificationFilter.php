<?php

namespace App\Filters\Dictionaries;

use App\Filters\BaseModelFilter;
use App\Models\Dictionaries\TireSpecification;
use App\Traits\Filter\ActiveFilterTrait;
use App\Traits\Filter\IdFilterTrait;
use App\Traits\Filter\ModeratedFilterTrait;
use App\Traits\Filter\SortFilterTrait;

class TireSpecificationFilter extends BaseModelFilter
{
    use IdFilterTrait;
    use ActiveFilterTrait;
    use SortFilterTrait;
    use ModeratedFilterTrait;

    public function tireMake(int $makeId): void
    {
        $this->where('make_id', $makeId);
    }

    public function tireModel(int $modelId): void
    {
        $this->where('model_id', $modelId);
    }

    public function tireType(int $typeId): void
    {
        $this->where('type_id', $typeId);
    }

    public function tireSize(int $sizeId): void
    {
        $this->where('size_id', $sizeId);
    }

    public function allowedOrders(): array
    {
        return TireSpecification::ALLOWED_SORTING_FIELDS;
    }
}
