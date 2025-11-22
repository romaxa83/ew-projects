<?php

namespace App\Filters\Dictionaries;

use App\Filters\BaseModelFilter;
use App\Models\Dictionaries\TireModel;
use App\Traits\Filter\ActiveFilterTrait;
use App\Traits\Filter\IdFilterTrait;
use App\Traits\Filter\LikeRawFilterTrait;
use App\Traits\Filter\ModeratedFilterTrait;
use App\Traits\Filter\SortFilterTrait;

class TireModelFilter extends BaseModelFilter
{
    use IdFilterTrait;
    use ActiveFilterTrait;
    use SortFilterTrait;
    use LikeRawFilterTrait;
    use ModeratedFilterTrait;

    public function query(string $query): void
    {
        $this->likeRaw('title',  $query);
    }

    public function tireMake(int $tireMakeId): void
    {
        $this->where('tire_make_id', $tireMakeId);
    }

    public function allowedOrders(): array
    {
        return TireModel::ALLOWED_SORTING_FIELDS;
    }
}
