<?php

declare(strict_types=1);

namespace Wezom\Core\ModelFilters;

use EloquentFilter\ModelFilter as BaseModelFilter;
use Wezom\Core\Traits\Filter\ActiveFilterTrait;
use Wezom\Core\Traits\Filter\IdFilterTrait;
use Wezom\Core\Traits\Filter\SortFilterTrait;

class ModelFilter extends BaseModelFilter
{
    use ActiveFilterTrait;
    use IdFilterTrait;
    use SortFilterTrait;
}
