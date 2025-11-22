<?php

namespace App\Models\Catalog\Products;

use App\Contracts\Favourites\Favorable;
use App\Contracts\Media\HasMedia;
use App\Enums\Catalog\Products\ProductOwnerType;
use App\Enums\Catalog\Products\ProductUnitSubType;
use App\Filters\Catalog\ProductFilter;
use App\Models\BaseModel;
use App\Models\Catalog\Brands\Brand;
use App\Models\Catalog\Categories\Category;
use App\Models\Catalog\Certificates\Certificate;
use App\Models\Catalog\Features\Feature;
use App\Models\Catalog\Features\Specification;
use App\Models\Catalog\Features\Value;
use App\Models\Catalog\Labels\Label;
use App\Models\Catalog\Manuals\Manual;
use App\Models\Catalog\Manuals\ManualGroup;
use App\Models\Catalog\Solutions\Solution;
use App\Models\Catalog\Tickets\Ticket;
use App\Models\Catalog\Troubleshoots;
use App\Models\Catalog\Videos\Group;
use App\Models\Catalog\Videos\VideoLink;
use App\Models\Projects\Pivot\SystemUnitPivot;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use App\Traits\Model\ActiveScopeTrait;
use App\Traits\Model\Favourites\FavourableTrait;
use App\Traits\Model\HasTranslations;
use App\Traits\Model\Media\InteractsWithMedia;
use App\Traits\Model\SetSortAfterCreate;
use App\Traits\Scopes\OwnerTypeScope;
use App\Traits\SimpleEloquent;
use Database\Factories\Catalog\Products\ProductFactory;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Image\Exceptions\InvalidManipulation;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @property int id
 * @property string title
 * @property ProductOwnerType owner_type
 * @property ProductUnitSubType unit_sub_type
 * @property array olmo_additions
 * @property int|null brand_id
 * @property int|null unit_type_id
 * @property string|null guid
 * @property bool show_rebate       // показывать на стр. rebate (default=false)
 *
 * @see System::units()
 * @property-read SystemUnitPivot unit
 *
 * @see Product::keywords()
 * @property-read Collection|ProductKeyword[] keywords
 *
 * @see Product::category()
 * @property-read Category category
 *
 * @see Product::brand()
 * @property-read Brand brand
 *
 * @see Product::unitType()
 * @property-read UnitType unitType
 *
 * @see Product::serialNumbers()
 * @property-read Collection|ProductSerialNumber[] serialNumbers
 *
 * @see Product::certificates()
 * @property-read Collection|Certificate[] certificates
 *
 * @see Product::manuals()
 * @property-read Collection|ManualGroup[] manuals
 *
 * @see Product::videoLinks()
 * @property-read Collection|VideoLink[] videoLinks
 *
 * @see Product::videoGroups()
 * @property-read Collection|Group[] videoGroups
 *
 * @see Product::values()
 * @property-read Collection|Value[] values
 *
 * @see Product::mobileValues()
 * @property-read Collection|Value[] mobileValues
 *
 * @see Product::webValues()
 * @property-read Collection|Value[] webValues
 *
 * @see Product::labels()
 * @property-read Collection|Label[] labels
 *
 * @see Product::scopeCooper()
 * @method Builder|static cooper()
 * @see Product::scopeOlmo()
 * @method Builder|static olmo()
 *
 * @method static ProductFactory factory(...$options)
 */
class Product extends BaseModel implements HasMedia, Favorable
{
    use HasTranslations;
    use Filterable;
    use HasFactory;
    use ActiveScopeTrait;
    use InteractsWithMedia;
    use SetSortAfterCreate;
    use FavourableTrait;
    use SimpleEloquent;
    use OwnerTypeScope;

    public const TABLE = 'catalog_products';
    public const MORPH_NAME = 'product';

    public const MEDIA_COLLECTION_NAME = 'product';
    public const MOBILE_CONVERSION = 'mobile';
    public const PRODUCT_CARD_CONVERSION = 'product_card';
    public const PDF_CARD_CONVERSION = 'pdf_card';

    public const ALLOWED_SORTING_FIELDS = [
        'sort',
        'title'
    ];

    protected $table = self::TABLE;

    protected $fillable = [
        'guid',
        'sort',
        'active',
        'slug',
        'title',
        'title_metaphone',
        'seer',
        'category_id',
        'brand_id',
        'unit_type_id',
        'created_at',
        'updated_at',
        'show_rebate'
    ];

    protected $casts = [
        'active' => 'boolean',
        'show_rebate' => 'boolean',
        'owner_type' => ProductOwnerType::class,
        'unit_sub_type' => ProductUnitSubType::class,
        'olmo_additions' => 'array',
    ];

    public function getMediaCollectionName(): string
    {
        return self::MEDIA_COLLECTION_NAME;
    }

    public function getMultiLangMediaCollectionName(string $lang): string
    {
        return $lang . '_' . self::MEDIA_COLLECTION_NAME;
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::MEDIA_COLLECTION_NAME)
            ->acceptsMimeTypes(
                array_merge(
                    $this->mimeImage(),
                    $this->mimeVideo(),
                )
            );
    }

    /** @throws InvalidManipulation */
    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion($this->getOriginalWebpConversionName())
            ->format(Manipulations::FORMAT_WEBP);

        $this->addMediaConversion(self::PRODUCT_CARD_CONVERSION)
            ->height(209 * 2)
            ->width(175 * 2);

        $this->addMediaConversion(self::PDF_CARD_CONVERSION)
            ->height(85 * 2)
            ->width(85 * 2);

        $this->addMediaConversion(self::MOBILE_CONVERSION)
            ->width(720)
            ->height(370);
    }

    public function modelFilter(): string
    {
        return ProductFilter::class;
    }

    public function keywords(): HasMany|ProductKeyword
    {
        return $this->hasMany(ProductKeyword::class);
    }

    public function category(): BelongsTo|Category
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class, 'brand_id', 'id');
    }

    public function unitType(): BelongsTo|UnitType
    {
        return $this->belongsTo(UnitType::class, 'unit_type_id', 'id');
    }

    public function videoLinks(): BelongsToMany|VideoLink
    {
        return $this->belongsToMany(
            VideoLink::class,
            'catalog_product_video_link_pivot',
            'product_id',
            'link_id'
        )
            ->oldest('sort');
    }

    public function troubleshootGroups(): BelongsToMany|Troubleshoots\Group
    {
        return $this->belongsToMany(
            Troubleshoots\Group::class,
            'catalog_product_troubleshoot_groups_pivot',
            'product_id',
            'troubleshoot_group_id'
        )
            ->oldest('sort');
    }

    public function relationProducts(): BelongsToMany|Product
    {
        return $this->belongsToMany(
            self::class,
            ProductRelationPivot::TABLE,
            'product_id',
            'relation_id'
        )
            ->latest('sort');
    }

    public function relativeCategories(): BelongsToMany|Product
    {
        return $this->belongsToMany(
            Category::class,
            ProductRelativeCategoryPivot::TABLE,
            'product_id',
            'category_id'
        );
    }

    public function mobileValues(): BelongsToMany|Value
    {
        return $this->values()
            ->whereHas('feature', fn(Builder|Feature $q) => $q->where('display_in_mobile', true));
    }

    public function values(): BelongsToMany|Value
    {
        return $this->belongsToMany(
            Value::class,
            ProductFeatureValue::TABLE,
            'product_id',
            'value_id',
        )
            ->using(ProductFeatureValue::class);
    }

    public function webValues(): BelongsToMany|Value
    {
        return $this->values()
            ->whereHas('feature', fn(Builder|Feature $q) => $q->where('display_in_web', true));
    }

    public function serialNumbers(): HasMany|ProductSerialNumber
    {
        return $this->hasMany(ProductSerialNumber::class);
    }

    public function certificates(): BelongsToMany|Certificate
    {
        return $this->belongsToMany(Certificate::class);
    }

    public function labels(): BelongsToMany
    {
        return $this->belongsToMany(
            Label::class,
            'product_label',
            'product_id',
            'label_id'
        )
            ->latest('sort')
            ;
    }

    public function manuals(): BelongsToMany|Manual
    {
        return $this->belongsToMany(Manual::class);
    }

    public function specifications(): BelongsToMany|Specification
    {
        return $this->belongsToMany(
            Specification::class,
            ProductSpecificationPivot::TABLE,
            'product_id',
            'specification_id'
        )
            ->latest('sort');
    }

    public function tickets(): HasManyThrough
    {
        return $this->hasManyThrough(
            Ticket::class,
            ProductSerialNumber::class,
            'product_id',
            'serial_number',
            'id',
            'serial_number',
        );
    }

    public function solution(): HasOne
    {
        return $this->hasOne(Solution::class, 'product_id', 'id');
    }

    public function scopeJoinValues(Builder $builder): void
    {
        $builder
            ->select(
                array_merge(
                    [
                        $this->table . '.*'
                    ],
                    array_map(
                        static fn(string $item) => Value::TABLE . '.' . $item . ' AS ' . Value::TABLE . '_' . $item,
                        (new Value())->getConnection()->getSchemaBuilder()->getColumnListing(Value::TABLE)
                    )
                )
            )
            ->join(
                ProductFeatureValue::TABLE,
                $this->table . '.id',
                '=',
                ProductFeatureValue::TABLE . '.product_id',
            )
            ->join(
                Value::TABLE,
                ProductFeatureValue::TABLE . '.value_id',
                '=',
                Value::TABLE . '.id'
            );
    }

    public function getFavorableType(): string
    {
        return self::MORPH_NAME;
    }

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @throws Exception
     */
    public function setBreadcrumbs(): self
    {
        if (!empty($this->category_id)) {
            $this->breadcrumbs = categoryStorage()->buildBreadcrumbs($this->category_id);
        }

        return $this;
    }

    public function getImgUrl(): ?string
    {
        if($this->owner_type->isCooper()){
            if($this->media->isNotEmpty()){
                return $this->media[0]->getFullUrl();
            }
        }
        if($this->owner_type->isOlmo()){
            return data_get($this->olmo_additions, 'media.0');
        }

        return null;
    }

    public function getFrontLink(): string
    {
        return config('app.site_url') . "/product/{$this->slug}";
    }
}
