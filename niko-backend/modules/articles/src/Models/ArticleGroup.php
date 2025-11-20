<?php

namespace WezomCms\Articles\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use WezomCms\Core\ExtendPackage\Translatable;
use WezomCms\Core\Traits\Model\Filterable;
use WezomCms\Core\Traits\Model\GetForSelectTrait;
use WezomCms\Core\Traits\Model\ImageAttachable;
use WezomCms\Core\Traits\Model\PublishedTrait;

/**
 * \WezomCms\Articles\Models\ArticleGroup
 *
 * @property int $id
 * @property int $sort
 * @property string|null $image
 * @property bool $published
 * @property string|null $slug
 * @property string|null $name
 * @property string|null $title
 * @property string|null $h1
 * @property string|null $keywords
 * @property string|null $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection|Article[] $articles
 * @property-read Collection|ArticleGroupTranslation[] $translations
 * @method static Builder|ArticleGroup filter($input = array(), $filter = null)
 * @method static Builder|ArticleGroup listsTranslations($translationField)
 * @method static Builder|ArticleGroup newModelQuery()
 * @method static Builder|ArticleGroup newQuery()
 * @method static Builder|ArticleGroup notTranslatedIn($locale = null)
 * @method static Builder|ArticleGroup orWhereTranslation($translationField, $value, $locale = null)
 * @method static Builder|ArticleGroup orWhereTranslationLike($translationField, $value, $locale = null)
 * @method static Builder|ArticleGroup orderByTranslation($translationField, $sortMethod = 'asc')
 * @method static Builder|ArticleGroup paginateFilter($perPage = null, $columns = array(), $pageName = 'page', $page = null)
 * @method static Builder|ArticleGroup published()
 * @method static Builder|ArticleGroup publishedWithSlug($slug, $slugField = 'slug')
 * @method static Builder|ArticleGroup query()
 * @method static Builder|ArticleGroup simplePaginateFilter($perPage = null, $columns = array(), $pageName = 'page', $page = null)
 * @method static Builder|ArticleGroup translated()
 * @method static Builder|ArticleGroup translatedIn($locale = null)
 * @method static Builder|ArticleGroup whereBeginsWith($column, $value, $boolean = 'and')
 * @method static Builder|ArticleGroup whereCreatedAt($value)
 * @method static Builder|ArticleGroup whereEndsWith($column, $value, $boolean = 'and')
 * @method static Builder|ArticleGroup whereId($value)
 * @method static Builder|ArticleGroup whereImage($value)
 * @method static Builder|ArticleGroup whereLike($column, $value, $boolean = 'and')
 * @method static Builder|ArticleGroup wherePublished($value)
 * @method static Builder|ArticleGroup whereSort($value)
 * @method static Builder|ArticleGroup whereTranslation($translationField, $value, $locale = null)
 * @method static Builder|ArticleGroup whereTranslationLike($translationField, $value, $locale = null)
 * @method static Builder|ArticleGroup whereUpdatedAt($value)
 * @method static Builder|ArticleGroup withTranslation()
 * @mixin \Eloquent
 * @mixin ArticleGroupTranslation
 */
class ArticleGroup extends Model
{
    use Filterable;
    use GetForSelectTrait;
    use Translatable;
    use PublishedTrait;
    use ImageAttachable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['published'];

    /**
     * Names of the fields being translated in the "Translation" model.
     *
     * @var array
     */
    public $translatedAttributes = ['slug', 'name', 'short_description', 'title', 'h1', 'keywords', 'description'];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = ['translations'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['published' => 'boolean'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function articles()
    {
        return $this->hasMany(Article::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany|mixed
     */
    public function publishedArticles()
    {
        return $this->articles()->scopes(['published']);
    }

    /**
     * @return array
     */
    public function imageSettings(): array
    {
        return ['image' => 'cms.articles.groups.images'];
    }

    /**
     * @return \Illuminate\Contracts\Routing\UrlGenerator|string
     */
    public function getFrontUrl()
    {
        return route_localized('article-groups.inner', $this->slug);
    }
}
