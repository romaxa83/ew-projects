<?php

namespace App\Filters\Catalog;

use App\Filters\BaseModelFilter;
use App\Models\Catalog\Products\ProductKeyword;
use App\Traits\Filter\IdFilterTrait;

/**
 * @mixin ProductKeyword
 */
class ProductKeywordFilter extends BaseModelFilter
{
    use IdFilterTrait;
}