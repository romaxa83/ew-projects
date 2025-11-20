<?php

namespace WezomCms\Articles\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use WezomCms\Core\ExtendPackage\Translatable;
use WezomCms\Core\Traits\Model\Filterable;
use WezomCms\Core\Traits\Model\ImageAttachable;
use WezomCms\Core\Traits\Model\PrevNextTrait;
use WezomCms\Core\Traits\Model\PublishedTrait;

/**
 * \WezomCms\Articles\Models\Article
 *
 * @property int $id
 * @property int|null $article_group_id
 * @property string|null $image
 * @property bool $published
 * @property string|null $slug
 * @property string|null $name
 * @property string|null $text
 * @property string|null $title
 * @property string|null $h1
 * @property string|null $keywords
 * @property string|null $description
 * @property Carbon $published_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read ArticleGroup|null $group
 * @property-read Collection|ArticleTranslation[] $translations
 * @method static Builder|Article filter($input = array(), $filter = null)
 * @method static Builder|Article listsTranslations($translationField)
 * @method static Builder|Article newModelQuery()
 * @method static Builder|Article newQuery()
 * @method static Builder|Article notTranslatedIn($locale = null)
 * @method static Builder|Article orWhereTranslation($translationField, $value, $locale = null)
 * @method static Builder|Article orWhereTranslationLike($translationField, $value, $locale = null)
 * @method static Builder|Article orderByTranslation($translationField, $sortMethod = 'asc')
 * @method static Builder|Article paginateFilter($perPage = null, $columns = array(), $pageName = 'page', $page = null)
 * @method static Builder|Article published()
 * @method static Builder|Article publishedWithSlug($slug, $slugField = 'slug')
 * @method static Builder|Article query()
 * @method static Builder|Article simplePaginateFilter($perPage = null, $columns = array(), $pageName = 'page', $page = null)
 * @method static Builder|Article translated()
 * @method static Builder|Article translatedIn($locale = null)
 * @method static Builder|Article whereArticleGroupId($value)
 * @method static Builder|Article whereBeginsWith($column, $value, $boolean = 'and')
 * @method static Builder|Article whereCreatedAt($value)
 * @method static Builder|Article whereEndsWith($column, $value, $boolean = 'and')
 * @method static Builder|Article whereId($value)
 * @method static Builder|Article whereImage($value)
 * @method static Builder|Article whereLike($column, $value, $boolean = 'and')
 * @method static Builder|Article wherePublishedAt($value)
 * @method static Builder|Article whereTranslation($translationField, $value, $locale = null)
 * @method static Builder|Article whereTranslationLike($translationField, $value, $locale = null)
 * @method static Builder|Article whereUpdatedAt($value)
 * @method static Builder|Article withTranslation()
 * @mixin \Eloquent
 * @mixin ArticleTranslation
 */
class Article extends Model
{
    use Translatable;
    use ImageAttachable;
    use Filterable;
    use PublishedTrait;
    use PrevNextTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['published_at', 'article_group_id'];

    /**
     * Names of the fields being translated in the "Translation" model.
     *
     * @var array
     */
    public $translatedAttributes = ['published', 'slug', 'name', 'text', 'title', 'h1', 'keywords', 'description'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['published_at'];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = ['translations'];

    /**
     * @return array
     */
    public function imageSettings(): array
    {
        return ['image' => 'cms.articles.articles.images'];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function group()
    {
        return $this->belongsTo(ArticleGroup::class, 'article_group_id');
    }

    /**
     * @param $query
     */
    public function filterPublished($query)
    {
        $query->where('published_at', '<=', now());
    }

    /**
     * @param  Builder  $query
     */
    protected function filterPrevNextSelection(Builder $query)
    {
        $query->published()
            ->where('article_group_id', '=', $this->article_group_id);
    }

    /**
     * @return array
     */
    protected function getSortField()
    {
        return ['published_at', 'id'];
    }

    /**
     * @return string
     */
    protected function getSortType()
    {
        return 'DESC';
    }

    /**
     * @return \Illuminate\Contracts\Routing\UrlGenerator|string
     */
    public function getFrontUrl()
    {
        return route_localized('articles.inner', $this->slug);
    }

    /**
     * @return bool
     */
    public function canGoToFront(): bool
    {
        return $this->published_at <= now();
    }
}
