<?php

namespace App\Filters\Dictionaries;

use App\Filters\BaseModelFilter;
use App\Models\Dictionaries\TireSize;
use App\Traits\Filter\ActiveFilterTrait;
use App\Traits\Filter\IdFilterTrait;
use App\Traits\Filter\ModeratedFilterTrait;
use App\Traits\Filter\SortFilterTrait;

class TireSizeFilter extends BaseModelFilter
{
    use IdFilterTrait;
    use ActiveFilterTrait;
    use SortFilterTrait;
    use ModeratedFilterTrait;

    public function tireWidth(int $tireWidthId): void
    {
        $this->where('tire_width_id', $tireWidthId);
    }

    public function tireHeight(int $tireHeightId): void
    {
        $this->where('tire_height_id', $tireHeightId);
    }

    public function tireDiameter(int $tireDiameterId): void
    {
        $this->where('tire_diameter_id', $tireDiameterId);
    }

    public function allowedOrders(): array
    {
        return TireSize::ALLOWED_SORTING_FIELDS;
    }
}
