<?php

namespace App\Models\Catalog\Products;

use App\Models\BasePivot;

/**
 * @property int product_id
 * @property int value_id
 */
class ProductFeatureValue extends BasePivot
{
    public const TABLE = 'catalog_product_feature_value_pivot';
    protected $table = self::TABLE;

    /** @var null */
    protected $primaryKey = null;

    protected $fillable = [
        'product_id',
        'value_id',
    ];
}

