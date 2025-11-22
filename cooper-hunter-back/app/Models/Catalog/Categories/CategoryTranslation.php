<?php

namespace App\Models\Catalog\Categories;

use App\Models\BaseTranslation;
use App\Traits\HasFactory;
use App\Traits\Model\ActiveScopeTrait;
use App\Traits\SimpleEloquent;
use Database\Factories\Catalog\Categories\CategoryTranslationFactory;

/**
 * @property int id
 * @property string slug
 * @property string title
 * @property null|string description
 * @property int row_id
 * @property string|null language
 *
 * @method static CategoryTranslationFactory factory(...$options)
 */
class CategoryTranslation extends BaseTranslation
{
    use HasFactory;
    use ActiveScopeTrait;
    use SimpleEloquent;

    public $timestamps = false;

    public const TABLE = 'catalog_category_translations';

    protected $table = self::TABLE;

    protected $fillable = [
        'row_id',
        'language',
        'title',
        'description',
        'seo_title',
        'seo_description',
        'seo_h1',
    ];
}
