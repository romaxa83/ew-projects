<?php

namespace WezomCms\Catalog\Models\Specifications;

use Cviebrock\EloquentSluggable\Services\SlugService;
use Cviebrock\EloquentSluggable\Sluggable;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use WezomCms\Catalog\ModelFilters\SpecificationFilter;
use WezomCms\Catalog\Models\Product;
use WezomCms\Core\ExtendPackage\Translatable;
use WezomCms\Core\Traits\Model\Filterable;
use WezomCms\Core\Traits\Model\ImageAttachable;
use WezomCms\Core\Traits\Model\OrderBySort;
use WezomCms\Core\Traits\Model\PublishedTrait;

/**
 * \WezomCms\Catalog\Models\Specifications\Specification
 *
 * @property int $id
 * @property bool $published
 * @property string $slug
 * @property int $sort
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $image
 * @property int $collapse
 * @property string|null $type
 * @property int $important
 * @property-read Collection|SpecValue[] $publishedSpecValues
 * @property-read int|null $published_spec_values_count
 * @property-read Collection|SpecValue[] $specValues
 * @property-read int|null $spec_values_count
 * @property-read SpecificationTranslation $translation
 * @property-read Collection|SpecificationTranslation[] $translations
 * @property-read int|null $translations_count
 * @method static Builder|Specification filter($input = array(), $filter = null)
 * @method static Builder|Specification findSimilarSlugs($attribute, $config, $slug)
 * @method static Builder|Specification listsTranslations($translationField)
 * @method static Builder|Specification newModelQuery()
 * @method static Builder|Specification newQuery()
 * @method static Builder|Specification notTranslatedIn($locale = null)
 * @method static Builder|Specification orWhereTranslation($translationField, $value, $locale = null)
 * @method static Builder|Specification orWhereTranslationLike($translationField, $value, $locale = null)
 * @method static Builder|Specification orderByTranslation($translationField, $sortMethod = 'asc')
 * @method static Builder|Specification paginateFilter($perPage = null, $columns = array(), $pageName = 'page', $page = null)
 * @method static Builder|Specification published()
 * @method static Builder|Specification query()
 * @method static Builder|Specification simplePaginateFilter($perPage = null, $columns = array(), $pageName = 'page', $page = null)
 * @method static Builder|Specification sorting()
 * @method static Builder|Specification translated()
 * @method static Builder|Specification translatedIn($locale = null)
 * @method static Builder|Specification type($type)
 * @method static Builder|Specification whereBeginsWith($column, $value, $boolean = 'and')
 * @method static Builder|Specification whereCollapse($value)
 * @method static Builder|Specification whereCreatedAt($value)
 * @method static Builder|Specification whereEndsWith($column, $value, $boolean = 'and')
 * @method static Builder|Specification whereId($value)
 * @method static Builder|Specification whereImage($value)
 * @method static Builder|Specification whereImportant($value)
 * @method static Builder|Specification whereLike($column, $value, $boolean = 'and')
 * @method static Builder|Specification whereSlug($value)
 * @method static Builder|Specification whereSort($value)
 * @method static Builder|Specification wherePublished($value)
 * @method static Builder|Specification whereTranslation($translationField, $value, $locale = null, $method = 'whereHas', $operator = '=')
 * @method static Builder|Specification whereTranslationLike($translationField, $value, $locale = null)
 * @method static Builder|Specification whereType($value)
 * @method static Builder|Specification whereUpdatedAt($value)
 * @method static Builder|Specification withTranslation()
 * @mixin Eloquent
 * @mixin SpecificationTranslation
 */
class Specification extends Model
{
    use Translatable;
    use Filterable;
    use HasFactory;
    use ImageAttachable;
    use Sluggable;
    use PublishedTrait;
    use OrderBySort;

    public const COLOR = 'color';
    public const SIZE = 'size';

    protected $fillable = [
        'published',
        'slug',
        'multiple'
    ];

    protected $translatedAttributes = ['name'];

    protected $casts = [
        'published' => 'bool',
        'multiple' => 'bool'
    ];

    protected $with = ['translation'];

    /**
     * @return array
     */
    public function imageSettings(): array
    {
        return ['image' => 'cms.catalog.specifications.images'];
    }

    /**
     * @param Builder $query
     * @param $type
     * @return Builder
     */
    public function scopeType(Builder $query, $type)
    {
        return $query->where('type', $type);
    }

    public function specValues(): HasMany
    {
        return $this->hasMany(SpecValue::class);
    }

    public function publishedSpecValues(): HasMany
    {
        return $this->hasMany(SpecValue::class)->published();
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(
            Product::class,
            'product_specifications',
            'spec_id',
            'product_id'
        );
    }

    /**
     * @return mixed
     */
    public function getValuesForSelect()
    {
        $result = $this->specValues->pluck('name', 'id');

        if (!$this->multiple) {
            $result->prepend(__('cms-core::admin.layout.Not set'), '');
        }

        return $result->toArray();
    }

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name',
            ],
        ];
    }

    public function setSlugAttribute($value): void
    {
        $this->attributes['slug'] = SlugService::createSlug($this, 'slug', (string)$value);
    }

    public function isColor(): bool
    {
        return $this->type === static::COLOR;
    }

    protected function modelFilter(): string
    {
        return SpecificationFilter::class;
    }
}
