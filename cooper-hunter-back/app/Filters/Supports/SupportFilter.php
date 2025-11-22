<?php

declare(strict_types=1);

namespace App\Filters\Supports;

use App\Filters\BaseModelFilter;
use App\Models\Support\Supports\Support;
use App\Traits\Filter\ActiveFilterTrait;
use App\Traits\Filter\SortFilterTrait;

/**
 * @mixin Support
 */
class SupportFilter extends BaseModelFilter
{
    use ActiveFilterTrait;
    use SortFilterTrait;

    public const TABLE = Support::TABLE;
}
