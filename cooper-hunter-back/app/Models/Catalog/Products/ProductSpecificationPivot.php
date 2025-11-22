<?php

namespace App\Models\Catalog\Products;

use App\Models\BasePivot;

/**
 * @property int product_id
 * @property int specification_id
 */
class ProductSpecificationPivot extends BasePivot
{
    public const TABLE = 'product_specification';

    protected $table = self::TABLE;

    /** @var null */
    protected $primaryKey = null;

    protected $fillable = [
        'product_id',
        'specification_id',
    ];
}
