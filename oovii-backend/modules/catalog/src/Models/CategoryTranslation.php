<?php

namespace WezomCms\Catalog\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use WezomCms\Core\Traits\Model\MultiLanguageSluggableTrait;

/**
 * WezomCms\Catalog\Models\CategoryTranslation
 *
 * @property int $id
 * @property int $category_id
 * @property string|null $name
 * @property string|null $slug
 * @property string|null $text
 * @property string|null $title
 * @property string|null $h1
 * @property string|null $keywords
 * @property string|null $description
 * @property string $locale
 * @method static Builder|CategoryTranslation newModelQuery()
 * @method static Builder|CategoryTranslation newQuery()
 * @method static Builder|CategoryTranslation query()
 * @method static Builder|CategoryTranslation whereCategoryId($value)
 * @method static Builder|CategoryTranslation whereDescription($value)
 * @method static Builder|CategoryTranslation whereH1($value)
 * @method static Builder|CategoryTranslation whereId($value)
 * @method static Builder|CategoryTranslation whereKeywords($value)
 * @method static Builder|CategoryTranslation whereLocale($value)
 * @method static Builder|CategoryTranslation whereName($value)
 * @method static Builder|CategoryTranslation whereSlug($value)
 * @method static Builder|CategoryTranslation whereText($value)
 * @method static Builder|CategoryTranslation whereTitle($value)
 * @mixin \Eloquent
 */
class CategoryTranslation extends EloquentModel
{
    use MultiLanguageSluggableTrait;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'slug', 'text', 'title', 'h1', 'keywords', 'description'];
}
