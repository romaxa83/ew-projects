<?php

namespace App\Filters\Tires;

use App\Filters\BaseModelFilter;
use App\Models\Tires\Tire;
use App\Traits\Filter\ActiveFilterTrait;
use App\Traits\Filter\IdFilterTrait;
use App\Traits\Filter\LikeRawFilterTrait;
use App\Traits\Filter\ModeratedFilterTrait;
use App\Traits\Filter\SortFilterTrait;
use Illuminate\Database\Eloquent\Builder;

class TireFilter extends BaseModelFilter
{
    use IdFilterTrait;
    use ActiveFilterTrait;
    use SortFilterTrait;
    use LikeRawFilterTrait;
    use ModeratedFilterTrait;

    public function query(string $query): void
    {
        $this->likeRaw('serial_number',  $query);
    }

    public function tireMake(int $makeId): void
    {
        $this->whereHas(
            'specification',
            fn(Builder $builder) => $builder->where('make_id', $makeId)
        );
    }

    public function tireModel(int $modelId): void
    {
        $this->whereHas(
            'specification',
            fn(Builder $builder) => $builder->where('model_id', $modelId)
        );
    }

    public function tireType(int $typeId): void
    {
        $this->whereHas(
            'specification',
            fn(Builder $builder) => $builder->where('type_id', $typeId)
        );
    }

    public function tireRelationshipType(int $typeId): void
    {
        $this->where('relationship_type_id', $typeId);
    }

    public function tireSize(int $sizeId): void
    {
        $this->whereHas(
            'specification',
            fn(Builder $builder) => $builder->where('size_id', $sizeId)
        );
    }

    public function allowedOrders(): array
    {
        return Tire::ALLOWED_SORTING_FIELDS;
    }
}
