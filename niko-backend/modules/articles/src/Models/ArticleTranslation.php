<?php

namespace WezomCms\Articles\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use WezomCms\Core\Traits\Model\MultiLanguageSluggableTrait;

/**
 * \WezomCms\Articles\Models\ArticleTranslation
 *
 * @property int $id
 * @property int $article_id
 * @property bool $published
 * @property string|null $slug
 * @property string|null $name
 * @property string|null $text
 * @property string|null $title
 * @property string|null $h1
 * @property string|null $keywords
 * @property string|null $description
 * @property string $locale
 * @method static Builder|ArticleTranslation findSimilarSlugs($attribute, $config, $slug)
 * @method static Builder|ArticleTranslation newModelQuery()
 * @method static Builder|ArticleTranslation newQuery()
 * @method static Builder|ArticleTranslation query()
 * @method static Builder|ArticleTranslation whereArticleId($value)
 * @method static Builder|ArticleTranslation whereDescription($value)
 * @method static Builder|ArticleTranslation whereH1($value)
 * @method static Builder|ArticleTranslation whereId($value)
 * @method static Builder|ArticleTranslation whereKeywords($value)
 * @method static Builder|ArticleTranslation whereLocale($value)
 * @method static Builder|ArticleTranslation whereName($value)
 * @method static Builder|ArticleTranslation wherePublished($value)
 * @method static Builder|ArticleTranslation whereSlug($value)
 * @method static Builder|ArticleTranslation whereText($value)
 * @method static Builder|ArticleTranslation whereTitle($value)
 * @mixin \Eloquent
 */
class ArticleTranslation extends Model
{
    use MultiLanguageSluggableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['published', 'slug', 'name', 'text', 'title', 'h1', 'keywords', 'description'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['published' => 'boolean'];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
}
