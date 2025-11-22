<?php

namespace App\Models\Catalog\Products;

use App\Models\BasePivot;

/**
 * @property int product_id
 * @property int relation_id
 */
class ProductRelationPivot extends BasePivot
{
    public const TABLE = 'catalog_product_relations_pivot';
    protected $table = self::TABLE;

    /** @var null */
    protected $primaryKey = null;

    protected $fillable = [
        'product_id',
        'relation_id',
    ];
}
