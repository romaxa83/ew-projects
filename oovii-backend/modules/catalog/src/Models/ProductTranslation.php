<?php

namespace WezomCms\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use WezomCms\Core\Traits\Model\MultiLanguageSluggableTrait;

/**
 * \WezomCms\Catalog\Models\ProductTranslation
 *
 * @property int $id
 * @property int $product_id
 * @property string $name
 * @property string|null $description
 * @property string|null $feature_1
 * @property string|null $feature_2
 * @property string|null $feature_3
 * @property string $locale
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\ProductTranslation findSimilarSlugs($attribute, $config, $slug)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\ProductTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\ProductTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\ProductTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\ProductTranslation whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\ProductTranslation whereH1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\ProductTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\ProductTranslation whereKeywords($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\ProductTranslation whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\ProductTranslation whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\ProductTranslation whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\ProductTranslation whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\ProductTranslation whereText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Catalog\Models\ProductTranslation whereTitle($value)
 * @mixin \Eloquent
 */
class ProductTranslation extends Model
{
//    use MultiLanguageSluggableTrait;

    public $timestamps = false;

    public const TABLE = 'product_translations';
    protected $table = self::TABLE;

    protected $fillable = [
        'locale',
        'name',
        'description',
        'feature_1',
        'feature_2',
        'feature_3'
    ];
}
