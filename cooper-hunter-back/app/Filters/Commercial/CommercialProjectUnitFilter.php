<?php

namespace App\Filters\Commercial;

use App\Filters\BaseModelFilter;
use App\Models\Commercial\CommercialProjectUnit;
use App\Traits\Filter\IdFilterTrait;

/**
 * @mixin CommercialProjectUnit
 */
class CommercialProjectUnitFilter extends BaseModelFilter
{
    use IdFilterTrait;

    public function commercialProject($value): void
    {
        $this->where('commercial_project_id', $value);
    }
}
