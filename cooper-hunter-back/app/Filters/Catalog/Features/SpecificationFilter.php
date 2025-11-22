<?php

namespace App\Filters\Catalog\Features;

use App\Filters\BaseModelFilter;
use App\Models\Catalog\Features\Specification;
use App\Traits\Filter\ActiveFilterTrait;
use App\Traits\Filter\IdFilterTrait;

/**
 * @mixin Specification
 */
class SpecificationFilter extends BaseModelFilter
{
    use IdFilterTrait;
    use ActiveFilterTrait;
}
