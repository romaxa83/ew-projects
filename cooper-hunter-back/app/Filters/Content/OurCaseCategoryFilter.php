<?php

namespace App\Filters\Content;

use App\Filters\BaseModelFilter;
use App\Models\Content\OurCases\OurCaseCategory;
use App\Traits\Filter\ActiveFilterTrait;
use App\Traits\Filter\IdFilterTrait;
use App\Traits\Filter\SlugFilterTrait;

/**
 * @mixin OurCaseCategory
 */
class OurCaseCategoryFilter extends BaseModelFilter
{
    use IdFilterTrait;
    use SlugFilterTrait;
    use ActiveFilterTrait;
}
