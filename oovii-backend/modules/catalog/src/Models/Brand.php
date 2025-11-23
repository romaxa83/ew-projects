<?php

namespace WezomCms\Catalog\Models;

use Cviebrock\EloquentSluggable\Services\SlugService;
use Cviebrock\EloquentSluggable\Sluggable;
use Eloquent;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use WezomCms\Core\ExtendPackage\Translatable;
use WezomCms\Core\Foundation\Helpers;
use WezomCms\Core\Traits\Model\Filterable;
use WezomCms\Core\Traits\Model\ImageAttachable;
use WezomCms\Core\Traits\Model\OrderBySort;
use WezomCms\Core\Traits\Model\PublishedTrait;

/**
 * \WezomCms\Catalog\Models\Brand
 *
 * @property int $id
 * @property bool $published
 * @property string $slug
 * @property string|null $image
 * @property int $sort
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection|Model[] $models
 * @property-read int|null $models_count
 * @property-read Collection|Product[] $products
 * @property-read int|null $products_count
 * @property-read BrandTranslation $translation
 * @property-read Collection|BrandTranslation[] $translations
 * @property-read int|null $translations_count
 * @method static Builder|Brand filter($input = array(), $filter = null)
 * @method static Builder|Brand findSimilarSlugs(string $attribute, array $config, string $slug)
 * @method static Builder|Brand listsTranslations($translationField)
 * @method static Builder|Brand newModelQuery()
 * @method static Builder|Brand newQuery()
 * @method static Builder|Brand notTranslatedIn($locale = null)
 * @method static Builder|Brand orWhereTranslation($translationField, $value, $locale = null)
 * @method static Builder|Brand orWhereTranslationLike($translationField, $value, $locale = null)
 * @method static Builder|Brand orderByTranslation($translationField, $sortMethod = 'asc')
 * @method static Builder|Brand paginateFilter($perPage = null, $columns = array(), $pageName = 'page', $page = null)
 * @method static Builder|Brand published()
 * @method static Builder|Brand publishedWithSlug($slug, $slugField = 'slug')
 * @method static Builder|Brand query()
 * @method static Builder|Brand simplePaginateFilter($perPage = null, $columns = array(), $pageName = 'page', $page = null)
 * @method static Builder|Brand sorting()
 * @method static Builder|Brand translated()
 * @method static Builder|Brand translatedIn($locale = null)
 * @method static Builder|Brand whereBeginsWith($column, $value, $boolean = 'and')
 * @method static Builder|Brand whereCreatedAt($value)
 * @method static Builder|Brand whereEndsWith($column, $value, $boolean = 'and')
 * @method static Builder|Brand whereId($value)
 * @method static Builder|Brand whereImage($value)
 * @method static Builder|Brand whereLike($column, $value, $boolean = 'and')
 * @method static Builder|Brand whereSlug($value)
 * @method static Builder|Brand whereSort($value)
 * @method static Builder|Brand wherePublished($value)
 * @method static Builder|Brand whereTranslation($translationField, $value, $locale = null, $method = 'whereHas', $operator = '=')
 * @method static Builder|Brand whereTranslationLike($translationField, $value, $locale = null)
 * @method static Builder|Brand whereUpdatedAt($value)
 * @method static Builder|Brand withTranslation()
 * @mixin Eloquent
 * @mixin BrandTranslation
 */
class Brand extends EloquentModel
{
    use Translatable;
    use Filterable;
    use HasFactory;
    use ImageAttachable;
    use PublishedTrait;
    use OrderBySort;
    use Sluggable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['published', 'slug'];

    /**
     * Names of the fields being translated in the "Translation" model.
     *
     * @var array
     */
    protected $translatedAttributes = ['name'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['published' => 'bool'];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = ['translation'];

    /**
     * @param string|null $search
     * @param array $criterion
     * @param int $limit
     * @return LengthAwarePaginator|Brand[]|array
     */
    public static function search(string $search = null, array $criterion = [], int $limit = 10)
    {
        $query = static::query();

        if ($search) {
            $query->whereTranslationLike('name', '%' . Helpers::escapeLike($search) . '%');
        }

        foreach ($criterion as $field => $value) {
            $query->where($field, $value);
        }

        return $query->paginate($limit);
    }

    /**
     * @param string|null $search
     * @param int $limit
     * @return array
     */
    public static function getForSelect(string $search = null, int $limit = 10)
    {
        $query = self::query();

        if ($search) {
            $query->whereTranslationLike('name', '%' . Helpers::escapeLike($search) . '%');
        }

        return $query->paginate($limit)
            ->pluck('name', 'id')
            ->sort()
            ->toArray();
    }

    /**
     * @return array
     */
    public function imageSettings(): array
    {
        return ['image' => 'cms.catalog.brands.images'];
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function models(): HasMany
    {
        return $this->hasMany(Model::class);
    }

    /**
     * Return the sluggable configuration array for this model.
     *
     * @return array
     */
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name',
            ],
        ];
    }

    /**
     * @param $value
     */
    public function setSlugAttribute($value)
    {
        $this->attributes['slug'] = SlugService::createSlug($this, 'slug', (string)$value);
    }
}
