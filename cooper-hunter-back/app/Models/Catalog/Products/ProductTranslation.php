<?php

namespace App\Models\Catalog\Products;

use App\Models\BaseTranslation;
use App\Traits\HasFactory;
use App\Traits\Model\ActiveScopeTrait;
use App\Traits\SimpleEloquent;
use Database\Factories\Catalog\Products\ProductTranslationFactory;

/**
 * @property int id
 * @property null|string description
 * @property int row_id
 * @property string|null language
 *
 * @method static ProductTranslationFactory factory(...$options)
 */
class ProductTranslation extends BaseTranslation
{
    use HasFactory;
    use ActiveScopeTrait;
    use SimpleEloquent;

    public $timestamps = false;

    public const TABLE = 'catalog_product_translations';

    protected $table = self::TABLE;

    protected $fillable = [
        'row_id',
        'language',
        'description',
        'seo_title',
        'seo_description',
        'seo_h1',
    ];
}
