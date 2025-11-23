<?php

namespace WezomCms\Catalog\Models;

use Carbon\CarbonImmutable;
use Eloquent;
use Greabock\Tentacles\EloquentTentacle;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection as SupportCollection;
use WezomCms\Catalog\Filter\Contracts\StorageInterface;
use WezomCms\Catalog\Models\Collections\Collection;
use WezomCms\Catalog\Models\Labels\Label;
use WezomCms\Catalog\Models\Specifications\Specification;
use WezomCms\Catalog\Models\Specifications\SpecValue;
use WezomCms\Catalog\Traits\ProductFlagsTrait;
use WezomCms\Catalog\Traits\ProductImageTrait;
use WezomCms\Catalog\Traits\PurchasedProductTrait;
use WezomCms\Core\Contracts\BelongsToAdminInterface;
use WezomCms\Core\ExtendPackage\Translatable;
use WezomCms\Core\Foundation\Helpers;
use WezomCms\Core\Models\Administrator;
use WezomCms\Core\Scopes\BelongsToAdminScope;
use WezomCms\Core\Traits\Model\Filterable;
use WezomCms\Core\Traits\Model\OrderBySort;
use WezomCms\Core\Traits\Model\PublishedTrait;
use WezomCms\Orders\Contracts\PurchasedProductInterface;
use WezomCms\ProductReviews\Models\ProductReview;
use WezomCms\Providers\Models\Provider;

/**
 * WezomCms\Catalog\Models\Product
 *
 * @property int $id
 * @property bool $published
 * @property int|null $moderator_id
 * @property int|null $provider_id
 * @property int|null $brand_id
 * @property int|null $category_id
 * @property float $cost
 * @property float $cost_discount
 * @property Carbon|null $expires_at
 * @property bool $available
 * @property bool $novelty
 * @property bool $popular
 * @property bool $sale
 * @property bool $best_price
 * @property int $sort
 * @property int $likes
 * @property int $dislikes
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $published_at
 * @property int $amount
 * @property int $amount_one_user
 * @property int|null $color_id
 * @property int $rating
 * @property string $group_key
 * @property int $weight
 * @property int $bonus
 * @property boolean $moderated
 * @property array|null $dimensions
 *
 * @see Product::getInCartAttribute()
 * @property-read bool $in_cart
 *
 * @see Product::reviews()
 * @property-read EloquentCollection|ProductReview[] $reviews
 *
 * @see Product::publishedReviews()
 * @property-read EloquentCollection|ProductReview[] $publishedReviews
 *
 * @see Product::rootReviews()
 * @property-read EloquentCollection|ProductReview[] $rootReviews
 *
 * @see Product::getLikesReviewsAttribute()
 * @property-read int $likes_reviews
 *
 * @see Product::getDislikesReviewsAttribute()
 * @property-read int $dislikes_reviews
 *
 * @see Product::providerProfile()
 * @property-read Provider|null $providerProfile
 *
 * @see Product::moderator()
 * @property-read Administrator|null $moderator
 *
 * @see Product::administrator()
 * @property-read Administrator|null $administrator
 *
 * @see Product::publishedSpecifications()
 * @property-read EloquentCollection|SpecValue[] $publishedSpecifications
 *
 * @see Product::collections()
 * @property-read EloquentCollection|Collection[] $collections
 *
 * @see Product::labels()
 * @property-read EloquentCollection|Label[] $labels
 *
 * @see Product::availableRelations()
 * @property-read EloquentCollection|Product[] $availableRelations
 *
 *  @see Product::availableRelationsActiveCollection()
 * @property-read EloquentCollection|Product[] $availableRelationsActiveCollection
 *
 * @property-read Brand|null $brand
 * @property-read Category|null $category
 * @property-read SpecValue $color
 * @property-read EloquentCollection|Product[] $combinedProducts
 * @property-read int|null $combined_products_count
 * @property-read string $flag_color
 * @property-read string $flag_text
 * @property-read SupportCollection $flags
 * @property-read EloquentCollection|ProductImage[]|string[] $gallery
 * @property-read bool $has_flag
 * @property-read string|null $image_alt
 * @property-read string|null $image_title
 * @property-read SupportCollection|Product[] $variations
 * @property-read EloquentCollection|ProductImage[] $images
 * @property-read int|null $images_count
 * @property-read EloquentCollection|ProductImage[] $mainImage
 * @property-read int|null $main_image_count
 * @property-read Model|null $model
 * @property-read EloquentCollection|ProductSpecification[] $productSpecifications
 * @property-read int|null $product_specifications_count
 * @property-read EloquentCollection|Specification[] $specifications
 * @property-read int|null $specifications_count
 * @property-read EloquentCollection|SpecValue[] $specificationsValues
 * @property-read int|null $specifications_values_count
 * @property-read ProductTranslation $translation
 * @property-read EloquentCollection|ProductTranslation[] $translations
 * @property-read int|null $translations_count
 * @method static EloquentBuilder|Product filter($input = [], $filter = null)
 * @method static bool|null forceDelete()
 * @method static EloquentBuilder|Product listsTranslations($translationField)
 * @method static EloquentBuilder|Product newModelQuery()
 * @method static EloquentBuilder|Product newQuery()
 * @method static EloquentBuilder|Product notTranslatedIn($locale = null)
 * @method static Builder|Product onlyTrashed()
 * @method static EloquentBuilder|Product orWhereTranslation($translationField, $value, $locale = null)
 * @method static EloquentBuilder|Product orWhereTranslationLike($translationField, $value, $locale = null)
 * @method static EloquentBuilder|Product orderByTranslation($translationField, $sortMethod = 'asc')
 * @method static EloquentBuilder|Product paginateFilter($perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static EloquentBuilder|Product published()
 * @method static EloquentBuilder|Product publishedWithSlug($slug, $slugField = 'slug')
 * @method static EloquentBuilder|Product query()
 * @method static bool|null restore()
 * @method static EloquentBuilder|Product simplePaginateFilter($perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static EloquentBuilder|Product sorting()
 * @method static EloquentBuilder|Product translated()
 * @method static EloquentBuilder|Product translatedIn($locale = null)
 * @method static EloquentBuilder|Product whereAvailable($value)
 * @method static EloquentBuilder|Product whereBeginsWith($column, $value, $boolean = 'and')
 * @method static EloquentBuilder|Product whereBrandId($value)
 * @method static EloquentBuilder|Product whereCategoryId($value)
 * @method static EloquentBuilder|Product whereColorId($value)
 * @method static EloquentBuilder|Product whereCost($value)
 * @method static EloquentBuilder|Product whereCreatedAt($value)
 * @method static EloquentBuilder|Product whereDeletedAt($value)
 * @method static EloquentBuilder|Product whereDiscountPercentage($value)
 * @method static EloquentBuilder|Product whereEndsWith($column, $value, $boolean = 'and')
 * @method static EloquentBuilder|Product whereExpiresAt($value)
 * @method static EloquentBuilder|Product whereGroupKey($value)
 * @method static EloquentBuilder|Product whereId($value)
 * @method static EloquentBuilder|Product whereLike($column, $value, $boolean = 'and')
 * @method static EloquentBuilder|Product whereModelId($value)
 * @method static EloquentBuilder|Product whereNovelty($value)
 * @method static EloquentBuilder|Product whereOldCost($value)
 * @method static EloquentBuilder|Product wherePopular($value)
 * @method static EloquentBuilder|Product wherePublished($value)
 * @method static EloquentBuilder|Product whereRating($value)
 * @method static EloquentBuilder|Product whereSale($value)
 * @method static EloquentBuilder|Product whereSort($value)
 * @method static EloquentBuilder|Product whereTranslation($translationField, $value, $locale = null, $method = 'whereHas', $operator = '=')
 * @method static EloquentBuilder|Product whereTranslationLike($translationField, $value, $locale = null)
 * @method static EloquentBuilder|Product whereUpdatedAt($value)
 * @method static EloquentBuilder|Product whereVideos($value)
 * @method static EloquentBuilder|Product withTranslation()
 * @method static Builder|Product withTrashed()
 * @method static Builder|Product withoutTrashed()
 * @mixin Eloquent
 * @mixin ProductTranslation
 */
class Product extends EloquentModel implements StorageInterface, PurchasedProductInterface, BelongsToAdminInterface
{
    use EloquentTentacle;
    use Filterable;
    use HasFactory;
    use Translatable;
    use ProductFlagsTrait;
    use ProductImageTrait;
    use PublishedTrait;
    use SoftDeletes;
    use OrderBySort;
    use PurchasedProductTrait;

    public const DEFAULT_DIMENSION = 1;

    public const TABLE = 'products';
    protected $table = self::TABLE;

    protected $fillable = [
        'published',
        'published_at',
        'expires_at',
        'cost',
        'cost_discount',
        'category_id',
        'brand_id',
        'moderator_id',
        'provider_id',
        'amount',
        'amount_one_user',
        'available',
        'group_key',
        'novelty',
        'popular',
        'sale',
        'best_price',
        'weight',
        'bonus',
        'moderated',
        'dimensions',
    ];

    protected $translatedAttributes = [
        'name',
        'description',
        'feature_1',
        'feature_2',
        'feature_3',
    ];

    protected $casts = [
        'published' => 'bool',
        'available' => 'bool',
        'novelty' => 'bool',
        'popular' => 'bool',
        'sale' => 'bool',
        'best_price' => 'bool',
        'cost' => 'float',
        'cost_discount' => 'float',
        'moderated' => 'bool',
        'dimensions' => 'array',
    ];

    protected $dates = [
        'expires_at',
        'published_at',
    ];

    protected $with = ['translation', 'mainImage'];

    protected $attributes = [
        'cost_discount' => 0.0,
        'bonus' => 0,
    ];

    protected static function booted()
    {
        static::addGlobalScope(new BelongsToAdminScope());
    }

    public function setPublishedAtAttribute($value): void
    {
        $this->attributes['published_at'] = $value ?? Carbon::now();
    }

    public static function search(string $search = null, array $criterion = [], int $limit = 10): LengthAwarePaginator
    {
        $query = self::query()
//            ->withTrashed()
        ;

        if ($search) {
            $query->whereTranslationLike('name', '%' . Helpers::escapeLike($search) . '%');
        }

        if (!empty($criterion)) {
            foreach ($criterion as $field => $value) {
                if (in_array($field, ['category_id', 'provider_id'])) {
                    $query->where($field, $value);
                }
            }
        }

        return $query->paginate($limit);
    }

    public function hasGenderLabel(): bool
    {
        foreach ($this->labels as $label) {
            if ($label->isGender()) {
                return true;
            }
        }

        return false;
    }

    public function hasSpecificationsColor(): bool
    {
        foreach ($this->specifications as $item) {
            /** @var $item Specification */
            if ($item->isColor()) {
                return true;
            }
        }

        return false;
    }

    public function moderator(): BelongsTo
    {
        return $this->belongsTo(Administrator::class, 'moderator_id', 'id');
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Administrator::class, 'provider_id', 'id');
    }

    public function administrator(): BelongsTo
    {
        return $this->belongsTo(Administrator::class, 'provider_id', 'id');
    }

    public function providerProfile(): HasOneThrough
    {
        return $this->hasOneThrough(
            Provider::class,
            Administrator::class,
            'id',
            'admin_id',
            'provider_id',
            'id'
        );
    }

    public function collections(): BelongsToMany
    {
        return $this->belongsToMany(
            Collection::class,
            'collection_product_relations',
            'product_id',
            'collection_id'
        );
    }

    public function relations(): BelongsToMany
    {
        return $this->belongsToMany(
            self::class,
            'product_relations',
            'product_id',
            'relation_id'
        );
    }

    public function availableRelations(): BelongsToMany
    {
        return $this->relations()->available();
    }

    public function availableRelationsActiveCollection(): BelongsToMany
    {
        return $this->availableRelations()->whereHas('collections', function ($q){
            $q->where('published', true);
        });
    }

    public function labels(): BelongsToMany
    {
        return $this->belongsToMany(
            Label::class,
            'product_label_relations',
            'product_id',
            'label_id'
        );
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    public function publishedReviews(): HasMany
    {
        return $this->hasMany(ProductReview::class)->published();
    }

    public function rootReviews(): HasMany
    {
        return $this->publishedReviews()->root()->latest();
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function model(): BelongsTo
    {
        return $this->belongsTo(Model::class);
    }

    public function specifications(): BelongsToMany
    {
        return $this->belongsToMany(
            Specification::class,
            'product_specifications',
            'product_id',
            'spec_id'
        );
    }

    public function color(): HasOneThrough
    {
        return $this->hasOneThrough(
            SpecValue::class,
            ProductSpecification::class,
            'product_id',
            'id',
            'id',
            'spec_value_id'
        )->whereHas(
            'specification',
            function ($query) {
                $query->whereType(Specification::COLOR);
            }
        );
    }

    public function combinedProducts(): HasMany
    {
        return $this->hasMany(Product::class, 'group_key', 'group_key')
            ->select('products.*')
            ->whereNotNull('group_key')
            ->with('color', 'mainImage')
            ->published();
    }

    public function publishedSpecifications(): mixed
    {
        return $this->specificationsValues()
            ->published()
            ->whereHas('specification', published_scope())
            ->with(['specification' => published_scope()]);
    }

    public function specificationsValues(): BelongsToMany
    {
        return $this->belongsToMany(SpecValue::class, 'product_specifications');
    }

    public function labelsArray(): array
    {
        return $this->labels->map(fn($item) => $item->translation->name)->toArray();
    }

    public function imagesUrlArray(?string $size = null): array
    {
        return $this->images->map(fn($item) => $item->getImageUrl($size))->toArray();
    }

    public function addView()
    {
        ViewedProducts::add($this->id);
    }

    public function getLikesReviewsAttribute(): int
    {
        return $this->rootReviews
            ->filter(fn (ProductReview $review) => $review->like)
            ->count();
    }

    public function getDislikesReviewsAttribute(): int
    {
        return $this->rootReviews
            ->filter(fn (ProductReview $review) => !$review->like)
            ->count();
    }

    public function setModeratedAttribute($value)
    {
        if (!$value) {
            $this->attributes['published'] = false;
        }

        $this->attributes['moderated'] = $value;
    }

    public function setPublishedAttribute($value)
    {
        $this->attributes['published'] = $this->moderated
            ? $value
            : false;
    }

    public function updateSpecValueRelation($specValues = [])
    {
        $this->productSpecifications()->delete();

        $specValues = array_filter($specValues);
        foreach ($specValues as $specId => $values) {
            foreach (array_filter($values) as $valueId) {
                $this->productSpecifications()->create(['spec_id' => $specId, 'spec_value_id' => $valueId]);
            }
        }
    }

    public function productSpecifications(): HasMany
    {
        return $this->hasMany(ProductSpecification::class);
    }

//    public function getFrontUrl(): string
//    {
//        return route('catalog.product', [$this->slug, $this->id]);
//    }

    /**
     * @return SupportCollection|Product[]
     */
    public function getVariationsAttribute()
    {
        $result = $this->combinedProducts;

        if ($result->count() < 2) {
            return collect();
        }

        return $result->sortBy('color.sort');
    }

    public function beginSelection(bool $fullSelection = true): mixed
    {
        $query = $this->query()
            ->select('products.*')
            ->published();

        if ($fullSelection) {
            $query->with('combinedProducts')->orderByDesc('available');
        }

        return $query;
    }

    public function beginCount(): mixed
    {
        return $this->query()->published();
    }

    public function getFrontUrl()
    {
        return route_localized('catalog.product', [$this->slug, $this->id]);
    }

    public function canGoToFront(): bool
    {
        return false;
    }

    public function scopeInStock(EloquentBuilder $query): EloquentBuilder
    {
        return $query->where('available', true)->where('amount', '>', 0);
    }

    public function scopeUnexpired(EloquentBuilder $query): EloquentBuilder
    {
        return $query->where(function(EloquentBuilder $query) {
            $query
                ->whereNull('expires_at')
                ->orWhere('expires_at', '>=', Carbon::now()->startOfDay());
            });
    }

    public static function sortDimensions(array $dimensions): array
    {
        return collect($dimensions)
            ->map(fn ($dimension) => is_numeric($dimension) ? (int) $dimension : self::DEFAULT_DIMENSION)
            ->sort()
            ->values()
            ->all();
    }

    public function getWeight(): int
    {
        return $this->weight;
    }

    public function getLength(): int
    {
        return is_array($this->dimensions) && count($this->dimensions)
            ? $this->dimensions[0]
            : self::DEFAULT_DIMENSION;
    }

    public function getWidth(): int
    {
        return is_array($this->dimensions) && (count($this->dimensions) > 1)
            ? $this->dimensions[1]
            : self::DEFAULT_DIMENSION;
    }

    public function getHeight(): int
    {
        return is_array($this->dimensions) && (count($this->dimensions) > 2)
            ? $this->dimensions[2]
            : self::DEFAULT_DIMENSION;
    }

    public function scopeAvailable(EloquentBuilder $query): EloquentBuilder
    {
        return $query->published()->inStock()->unexpired();
    }

    public function getFirstActiveCollection(): ?Collection
    {
        return $this->collections->where('published', true)
//            ->where('start_at', '<' , CarbonImmutable::now())
//            ->where('end_at', '>' , CarbonImmutable::now())
            ->first();
    }

    public static function formatCollectionDataForProduct(?Collection $collection = null): array
    {
        if (!$collection){
            return [];
        }

        return [
            'id' => $collection->id,
            'startAt' => $collection->start_at?->format(config('cms.core.time.format.start_at.api')),
            'endAt' => $collection->end_at?->format(config('cms.core.time.format.end_at.api')),
            'isReady' => $collection->start_at ? $collection->start_at < CarbonImmutable::now() : false,
        ];
    }
}
