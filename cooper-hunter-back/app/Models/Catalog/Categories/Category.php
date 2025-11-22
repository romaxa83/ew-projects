<?php

namespace App\Models\Catalog\Categories;

use App\Collections\Catalog\Categories\CategoryCollection;
use App\Contracts\Media\HasMedia;
use App\Contracts\Roles\HasGuardUser;
use App\Enums\Catalog\Products\ProductOwnerType;
use App\Enums\Categories\CategoryTypeEnum;
use App\Filters\Catalog\CategoryFilter;
use App\Models\Admins\Admin;
use App\Models\BaseModel;
use App\Models\Catalog\Products\Product;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use App\Traits\Model\ActiveScopeTrait;
use App\Traits\Model\HasTranslations;
use App\Traits\Model\Media\InteractsWithMedia;
use App\Traits\Model\SetSortAfterCreate;
use App\Traits\Scopes\CategoryTypeScope;
use App\Traits\Scopes\OwnerTypeScope;
use App\Traits\SimpleEloquent;
use BenSampo\Enum\Traits\CastsEnums;
use Database\Factories\Catalog\Categories\CategoryFactory;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Spatie\Image\Exceptions\InvalidManipulation;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @property ProductOwnerType owner_type
 * @property CategoryTypeEnum type
 *
 * @see Category::scopeCooper()
 * @method Builder|static cooper()
 * @see Category::scopeOlmo()
 * @method Builder|static olmo()
 * @see Category::scopeCommercial()
 * @method Builder|static commercial()
 *
 * @method static CategoryFactory factory(...$options)
 */
class Category extends BaseModel implements HasMedia
{
    use HasTranslations;
    use Filterable;
    use HasFactory;
    use ActiveScopeTrait;
    use InteractsWithMedia;
    use SetSortAfterCreate;
    use SimpleEloquent;
    use CastsEnums;
    use OwnerTypeScope;
    use CategoryTypeScope;

    public const TABLE = 'catalog_categories';
    public const MORPH_NAME = 'category';

    public const MEDIA_COLLECTION_NAME = 'category';
    public const POSTER_COLLECTION_NAME = 'poster';

    public const POSTER_CONVERSIONS = [
        'main' => [
            'width' => 369 * 2,
            'height' => 553 * 2,
        ],
    ];

    public const MEDIA_CONVERSIONS = [
        'small' => [
            'width' => 40 * 2,
            'height' => 32 * 2,
        ],
        'mobile' => [
            'width' => 120 * 2,
            'height' => 97 * 2,
        ],
        'website' => [
            'width' => 163 * 2,
            'height' => 110 * 2,
        ],
    ];

    public const MOBILE_CONVERSION = 'mobile';
    public const WEBSITE_CONVERSION = 'website';

    protected $table = self::TABLE;

    protected $fillable = [
        'sort',
        'active',
        'main',
        'enable_seer',
        'parent_id',
        'type',
        'slug',
        'guid',
    ];

    protected $casts = [
        'active' => 'boolean',
        'main' => 'boolean',
        'type' => CategoryTypeEnum::class,
        'owner_type' => ProductOwnerType::class,
    ];

    public static function getForSitemap(): array
    {
        return self::query()
            ->where('active', true)
            ->select(['id', 'slug', 'updated_at'])
            ->simple()
            ->get()
            ->toArray();
    }

    public static function getForSelect(callable $callback = null, array $args = []): array
    {
        $query = self::query()
            ->select(self::TABLE . '.*')
            ->addName()
            ->when(
                !(isset($args['with_olmo']) && $args['with_olmo']),
                static fn($b) => $b->cooper()
            )
            ->when(
                !isset($args['with_spares']) || (isset($args['with_spares']) && $args['with_spares'] == false),
                static fn($b) => $b->where('slug', '!=', 'spares')
            )
            ->latest('sort')
            ->getQuery();

        if (null !== $callback) {
            $callback($query);
        }

        return static::addTreeSpaces(
            groupByParentId($query->get())
        );
    }

    private static function addTreeSpaces(array $tree, $id = null, array &$result = [], $space = ''): array
    {
        foreach ($tree[$id] ?? [] as $group) {
            if (isset($tree[$group->id])) {
                $result[$group->id] =
                    [
                        'id' => $group->id,
                        //'disabled' => true,//клиент захотел разблокать все категории
                        'disabled' => false,
                        'name' => $space . $group->title
                    ];

                static::addTreeSpaces($tree, $group->id, $result, $space . '&nbsp;&nbsp;&nbsp;&nbsp;');
            } else {
                $result[$group->id] =
                    [
                        'id' => $group->id,
                        'disabled' => false,
                        'name' => $space . $group->title
                    ];
            }
        }

        return $result;
    }

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
            ->singleFile();

        $this->addMediaCollection(self::POSTER_COLLECTION_NAME)
            ->singleFile();
    }

    /** @throws InvalidManipulation */
    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion($this->getOriginalWebpConversionName())
            ->performOnCollections(self::MEDIA_COLLECTION_NAME, self::POSTER_COLLECTION_NAME)
            ->format(Manipulations::FORMAT_WEBP);

        foreach (self::MEDIA_CONVERSIONS as $name => $size) {
            $this->addMediaConversion($name)
                ->performOnCollections(self::MEDIA_COLLECTION_NAME)
                ->width($size['width'])
                ->height($size['height']);
        }

        foreach (self::POSTER_CONVERSIONS as $name => $size) {
            $this->addMediaConversion($name)
                ->performOnCollections(self::POSTER_COLLECTION_NAME)
                ->width($size['width'])
                ->height($size['height']);
        }
    }

    public function newCollection(array $models = []): CategoryCollection
    {
        return CategoryCollection::make($models);
    }

    public function modelFilter(): string
    {
        return CategoryFilter::class;
    }

    public function isRoot(): bool
    {
        return null === $this->parent_id;
    }

    public function image(): MorphOne
    {
        return $this->morphOne(\App\Models\Media\Media::class, 'model');
    }

    public function parent(): BelongsTo|static
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany|static
    {
        return $this->hasMany(self::class, 'parent_id', 'id');
    }

    public function products(): HasMany|Product
    {
        return $this->hasMany(Product::class, 'category_id', 'id');
    }

    public function getName(): string
    {
        return $this->hasAttribute('title')
            ? $this->title
            : $this->translation->title;
    }

    /**
     * @throws Exception
     */
    public function setBreadcrumbs(): self
    {
        if (!empty($this->parent_id)) {
            $this->breadcrumbs = categoryStorage()->buildBreadcrumbs($this->parent_id);
        }

        return $this;
    }

    public function scopeWithProducts(Builder|self $builder, ?HasGuardUser $user): void
    {
        return;

        if ($user instanceof Admin) {
            return;
        }
        $builder->whereHas('products');
    }

    public function scopeForGuard(Builder|self $builder, ?HasGuardUser $user): void
    {
        if ($user instanceof Admin) {
            return;
        }
        $builder->where('active', true);
    }
}
