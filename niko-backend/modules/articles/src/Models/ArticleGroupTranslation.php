<?php

namespace WezomCms\Articles\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use WezomCms\Core\Traits\Model\MultiLanguageSluggableTrait;

/**
 * WezomCms\Articles\Models\ArticleGroupTranslation
 *
 * @property int $id
 * @property int $article_group_id
 * @property string|null $slug
 * @property string|null $name
 * @property string|null $title
 * @property string|null $h1
 * @property string|null $keywords
 * @property string|null $description
 * @property string|null $short_description
 * @property string $locale
 * @method static Builder|ArticleGroupTranslation findSimilarSlugs($attribute, $config, $slug)
 * @method static Builder|ArticleGroupTranslation newModelQuery()
 * @method static Builder|ArticleGroupTranslation newQuery()
 * @method static Builder|ArticleGroupTranslation query()
 * @method static Builder|ArticleGroupTranslation whereArticleGroupId($value)
 * @method static Builder|ArticleGroupTranslation whereDescription($value)
 * @method static Builder|ArticleGroupTranslation whereH1($value)
 * @method static Builder|ArticleGroupTranslation whereId($value)
 * @method static Builder|ArticleGroupTranslation whereKeywords($value)
 * @method static Builder|ArticleGroupTranslation whereLocale($value)
 * @method static Builder|ArticleGroupTranslation whereName($value)
 * @method static Builder|ArticleGroupTranslation whereSlug($value)
 * @method static Builder|ArticleGroupTranslation whereShortDescription($value)
 * @method static Builder|ArticleGroupTranslation whereTitle($value)
 * @mixin \Eloquent
 */
class ArticleGroupTranslation extends Model
{
    use MultiLanguageSluggableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['slug', 'name', 'short_description', 'title', 'h1', 'keywords', 'description'];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
}
