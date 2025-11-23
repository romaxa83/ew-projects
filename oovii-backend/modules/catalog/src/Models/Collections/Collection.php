<?php

namespace WezomCms\Catalog\Models\Collections;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;
use WezomCms\Catalog\ModelFilters\CollectionFilter;
use WezomCms\Catalog\Models\Product;
use WezomCms\Core\ExtendPackage\Translatable;
use WezomCms\Core\Models\Administrator;
use WezomCms\Core\Traits\Hasher;
use WezomCms\Core\Traits\Model\Filterable;
use WezomCms\Core\Traits\Model\GetForSelectTrait;
use WezomCms\Core\Traits\Model\ImageAttachable;
use WezomCms\Core\Traits\Model\PublishedTrait;

/**
 * @property int $id
 * @property bool $published
 * @property int $creator_id
 * @property int|null $moderator_id
 * @property Carbon|null $start_at
 * @property Carbon|null $end_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property boolean $start_counter
 * @property boolean $end_counter
 * @property string $category_id
 * @property string $type
 * @property boolean $is_send_start
 * @property boolean $is_send_finish
 *
 * @property-read string hash_data
 *
 * @see Collection::products()
 * @property-read EloquentCollection|Product[] $products
 *
 * @see Collection::availableProducts()
 * @property-read EloquentCollection|Product[] $availableProducts
 *
 * @mixin CollectionTranslation
 */
class Collection extends EloquentModel
{
    use Translatable;
    use Filterable;
    use HasFactory;
    use GetForSelectTrait;
    use PublishedTrait;
    use ImageAttachable;
    use Hasher;

    public const TYPE_SOON = 'soon';
    public const TYPE_STOCK = 'stock';
    public const TYPE_ACTUAL = 'actual';

    public const START_AT_COUNTER = 'start';
    public const END_AT_COUNTER = 'end';

    public const TABLE = 'collections';
    protected $table = self::TABLE;

    protected $fillable = [
        'published',
        'creator_id',
        'moderator_id',
        'start_at',
        'end_at',
        'start_counter',
        'end_counter',
        'category_id',
        'type',
        'is_send_start',
        'is_send_finish',
    ];

    protected $translatedAttributes = ['name'];

    protected $casts = [
        'published' => 'bool',
        'start_counter' => 'bool',
        'end_counter' => 'bool',
        'is_send_start' => 'bool',
        'is_send_finish' => 'bool',
    ];

    protected $dates = [
        'start_at',
        'end_at',
    ];

    protected $with = ['translation'];

    public function imageSettings(): array
    {
        return ['image' => 'cms.catalog.collections.images'];
    }

    public function isPublished(): bool
    {
        return $this->published;
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(
            Product::class,
            'collection_product_relations',
            'collection_id',
            'product_id'
        );
    }

    public function availableProducts(): BelongsToMany
    {
        return $this->products()->available();
    }

    public function moderator(): BelongsTo
    {
        return $this->belongsTo(Administrator::class, "moderator_id", "id");
    }

    public function getTypePrettyAttribute(): string
    {
        return self::typeList()[$this->type];
    }

    public static function typeList(): array
    {
        return [
            self::TYPE_SOON => __('cms-catalog::admin.collection.type.soon'),
            self::TYPE_STOCK => __('cms-catalog::admin.collection.type.stock'),
            self::TYPE_ACTUAL => __('cms-catalog::admin.collection.type.actual'),
        ];
    }

    public function checkForPublished()
    {
        $now = Carbon::now();
        if ($this->end_at < $now) {
            return __(
                'cms-catalog::admin.collection.error_published.incorrect_end_at',
                [
                    'date' => $this->end_at
                ]
            );
        }
        if ($this->products->isEmpty()) {
            return __('cms-catalog::admin.collection.error_published.empty_product');
        }
        // проверяем наличие у товаров лейбла принадлежности, типа мужчинам, женщинам
        if ($msg = $this->hasProductsGenderLabel()) {
            return $msg;
        }
        // проверяем наличие у товаров характеристики color
        if ($msg = $this->hasProductsColor()) {
            return $msg;
        }

        return 0;
    }

    // можно ли опубликовать коллекцию, если да вернет 0
    // если нет вернет сообщение почему нельзя

    public function hasProductsGenderLabel()
    {
        foreach ($this->products as $product) {
            /** @var $product Product */
            if (!$product->hasGenderLabel()) {
                return __(
                    'cms-catalog::admin.collection.error_published.empty_gender_label',
                    [
                        'name' => $product->translation->name
                    ]
                );
            }
        }

        return 0;
    }

    public function hasProductsColor()
    {
        foreach ($this->products as $product) {
            /** @var $product Product */
            if (!$product->hasSpecificationsColor()) {
                return __(
                    'cms-catalog::admin.collection.error_published.empty_spec_color',
                    [
                        'name' => $product->translation->name
                    ]
                );
            }
        }

        return 0;
    }

    protected function modelFilter(): string
    {
        return CollectionFilter::class;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('end_at', '>', Carbon::now());
    }

    public function getHashDataAttribute(): string
    {
        return $this->hash([
            $this->start_at?->format(config('cms.core.time.format.start_at.api')),
            $this->end_at?->format(config('cms.core.time.format.end_at.api'))
        ]);
    }

    public function equalsHash(string $hash): bool
    {
        return $this->hash_data === $hash;
    }
}
