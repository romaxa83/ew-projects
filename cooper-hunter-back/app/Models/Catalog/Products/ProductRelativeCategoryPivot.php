<?php

namespace App\Models\Catalog\Products;

use App\Models\BasePivot;

class ProductRelativeCategoryPivot extends BasePivot
{
    public const TABLE = 'product_relative_category';

    public $timestamps = false;

    protected $table = self::TABLE;
}
