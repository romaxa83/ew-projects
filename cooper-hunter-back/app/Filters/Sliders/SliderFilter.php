<?php

declare(strict_types=1);

namespace App\Filters\Sliders;

use App\Filters\BaseModelFilter;
use App\Models\Sliders\Slider;
use App\Traits\Filter\ActiveFilterTrait;
use App\Traits\Filter\IdFilterTrait;
use App\Traits\Filter\SortFilterTrait;

/**
 * @mixin Slider
 */
class SliderFilter extends BaseModelFilter
{
    use IdFilterTrait;
    use ActiveFilterTrait;
    use SortFilterTrait;

    public const TABLE = Slider::TABLE;
}
