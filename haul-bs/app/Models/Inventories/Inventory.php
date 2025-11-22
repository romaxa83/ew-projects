<?php

namespace App\Models\Inventories;

use App\Contracts\Orders\Orderable;
use App\Enums\Inventories\InventoryPackageType;
use App\Enums\Inventories\InventoryStockStatus;
use App\Enums\Orders\BS\OrderStatus;
use App\Events\Events\Inventories\Inventories\ChangeQuantityInventory;
use App\Foundations\Models\BaseModel;
use App\Foundations\Modules\History\Contracts\HasHistory;
use App\Foundations\Modules\History\Traits\InteractsWithHistory;
use App\Foundations\Modules\Media\Contracts\HasMedia;
use App\Foundations\Modules\Media\Images\InventoryImage;
use App\Foundations\Modules\Media\Traits\InteractsWithMedia;
use App\Foundations\Modules\Seo\Contracts\HasSeo;
use App\Foundations\Modules\Seo\Traits\InteractsWithSeo;
use App\Foundations\Traits\Filters\Filterable;
use App\ModelFilters\Inventories\InventoryFilter;
use App\Models\Inventories\Features\Feature;
use App\Models\Orders\BS\Order;
use App\Models\Orders\Parts\Order as OrderParts;
use App\Models\Orders\BS\TypeOfWork as OrderTypeOfWork;
use App\Models\Orders\BS\TypeOfWorkInventory as OrderTypeOfWorkInventory;
use App\Models\Suppliers\Supplier;
use App\Models\TypeOfWorks\TypeOfWork;
use App\Models\TypeOfWorks\TypeOfWorkInventory;
use Database\Factories\Inventories\InventoryFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Eloquent;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @property int id
 * @property int|null category_id
 * @property int unit_id
 * @property int|null supplier_id
 * @property int|null brand_id
 * @property bool active
 * @property string name
 * @property string slug
 * @property string stock_number
 * @property string article_number
 * @property float price_retail
 * @property float|null min_limit_price
 * @property int quantity
 * @property int|null min_limit
 * @property string|null notes
 * @property bool for_shop
 * @property float|null length
 * @property float|null width
 * @property float|null height
 * @property float|null weight
 * @property int|null origin_id
 * @property bool is_new
 * @property bool is_popular
 * @property bool is_sale
 * @property float|null discount
 * @property float|null old_price
 * @property float|null delivery_cost
 * @property InventoryPackageType|null package_type
 * @property Carbon|null deleted_at
 * @property Carbon|null created_at
 * @property Carbon|null updated_at
 * @property bool already_sent_to_ecomm // отправлены ли данные в ecomm
 * @property bool new_item
 *
 * @see self::category()
 * @property Category|BelongsTo|null category
 *
 * @see self::supplier()
 * @property Supplier|BelongsTo supplier
 *
 * @see self::unit()
 * @property Unit|BelongsTo|null unit
 *
 * @see self::orders()
 * @property Order[]|HasManyThrough orders
 *
 * @see self::typesOfWork()
 * @property TypeOfWork[]|HasManyThrough typesOfWork
 *
 * @see self::brand()
 * @property Brand|BelongsTo|null brand
 *
 * @see self::features()
 * @property Feature[]|BelongsToMany features
 *
 * @see self::transactions()
 * @property Transaction[]|HasMany transactions
 *
 * @mixin Eloquent
 *
 * @method static InventoryFactory factory(...$parameters)
 */
class Inventory extends BaseModel implements
    HasSeo,
    HasMedia,
    HasHistory
{
    use HasFactory;
    use Filterable;
    use SoftDeletes;
    use InteractsWithSeo;
    use InteractsWithMedia;
    use InteractsWithHistory;

    public const TABLE = 'inventories';
    protected $table = self::TABLE;

    public const MORPH_NAME = 'inventory';

    public const MAIN_IMAGE_FIELD_NAME = 'main_image';
    public const GALLERY_FIELD_NAME = 'gallery';

    /** @var array<int, string> */
    protected $fillable = [
        'name',
        'slug',
        'category_id',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'active' => 'boolean',
        'for_shop' => 'boolean',
        'is_new' => 'boolean',
        'is_popular' => 'boolean',
        'is_sale' => 'boolean',
        'already_sent_to_ecomm' => 'boolean',
        'length' => 'double',
        'width' => 'double',
        'height' => 'double',
        'weight' => 'double',
        'price_retail' => 'float',
        'min_limit_price' => 'float',
        'discount' => 'float',
        'old_price' => 'float',
        'delivery_cost' => 'float',
        'quantity' => 'float',
        'new_item' => 'bool',
        'package_type' => InventoryPackageType::class,
    ];

//    protected static function boot() {
//        parent::boot();
//        static::updating(function ($product) {
//            if ($product->isDirty('price')) {
//                dd('up');
////                event(new PriceUpdated($product));
//            }
//        });
//    }

    public function modelFilter()
    {
        return $this->provideFilter(InventoryFilter::class);
    }

    public function getImageClass(): string
    {
        return InventoryImage::class;
    }

    public function getMainImg(): null|Media
    {
        return $this->getFirstImage(self::MAIN_IMAGE_FIELD_NAME);
    }

    public function getGallery(): array
    {
        return $this->getMedia(self::GALLERY_FIELD_NAME)
            ->all();
    }

    public function getStatus(): string
    {
        return $this->quantity > 0
            ? InventoryStockStatus::IN->value
            : InventoryStockStatus::OUT->value;
    }

    public function category(): ?BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function supplier(): ?BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function unit(): ?BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function brand(): ?BelongsTo
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'inventory_id');
    }

    public function dataForUpdateHistory(): array
    {
        $old = $this->getAttributes();
        $old['media'] = $this->media()->get();

        return $old;
    }

    public function features()
    {
        return $this->belongsToMany(
            Feature::class,
            InventoryFeature::TABLE,
            'inventory_id',
            'feature_id'
        )
            ->withPivot('value_id')
            ->orderBy('position')
            ;
    }

    public function getFeaturesWithValues(): Collection
    {
        $features = $this->features->groupBy('id')->map(function($featureItems) {
            $featureItems[0]->inventoryValues = $featureItems->first()->inventoryValues;
            return $featureItems[0];
        });

        return $features->values();
    }


    public function decreaseQuantity(float $quantity, bool $save = true): void
    {
        $this->quantity -= $quantity;
        if($save){
            $this->save();
        }
        if($this->for_shop){
            event(new ChangeQuantityInventory($this));
        }
    }

    public function increaseQuantity(float $quantity, bool $save = true): void
    {
        $this->quantity += $quantity;

        if($save){
            $this->save();
        }
        if($this->for_shop){
            event(new ChangeQuantityInventory($this));
        }
    }

    public function addTransaction(array $data, bool $fromReserve = false): Transaction
    {
        /** @var Transaction $transaction */
        $transaction = $this->transactions()->create($data);

        if (!$fromReserve && $transaction->isPurchase()) {
            $this->increaseQuantity($transaction->quantity);
        }

        if (!$fromReserve && $transaction->isSold()) {
            $this->decreaseQuantity($transaction->quantity);
        }

        return $transaction;
    }

    public function updateTransaction(
        Orderable $order,
        float $newQuantity,
        float $oldQuantity,
        ?float $price = null,
    ): ?Transaction
    {
        /** @var Transaction $transaction */
        $query = $this->transactions()
            ->where('quantity', $oldQuantity);

        if($order->isPartsOrder()){
            $query->where('order_parts_id', $order->getId());
        } else {
            $query->where('order_id', $order->getId());
        }

        /** @var Transaction $transaction */
        $transaction = $query->first();

        if ($transaction) {
            $transaction->quantity = $newQuantity;
            if($price){
                $transaction->price = $price;
            }
            $transaction->save();

            if ($newQuantity < $oldQuantity) {
                $this->increaseQuantity($oldQuantity - $newQuantity);
            } else {
                $this->decreaseQuantity($newQuantity - $oldQuantity);
            }
        }

        return $transaction;
    }

    public function changeReservedPrice(
        Order|OrderParts $order,
        ?float $price = null,
    ): void
    {
        if($order->isPartsOrder()){
            $this->transactions()
                ->where('order_parts_id', $order->id)
                ->update(['price' => $price ?? $this->price_retail]);
        } else {
            $this->transactions()
                ->where('order_id', $order->id)
                ->update(['price' => $price ?? $this->price_retail]);
        }
    }

    public function deleteReserve(
        Orderable $order,
        float $price,
        float $quantity
    ): void
    {
        $transaction = $this->transactions()
            ->where('price', $price)
            ->where('quantity', $quantity);

        if($order->isPartsOrder()){
            $transaction->where('order_parts_id', $order->getId());
        } else {
            $transaction->where('order_id', $order->getId());
        }

        $transaction->first();

        if ($transaction) {
            $transaction->delete();
        }
    }

    public function markAsReserve(Order $order): void
    {
        $this->transactions()
            ->where('order_id', $order->id)
            ->update(['is_reserve' => true]);
    }

    public function orders(): HasManyThrough
    {
        return $this->hasManyThrough(
            OrderTypeOfWork::class,
            OrderTypeOfWorkInventory::class,
            'inventory_id',
            'id',
            'id',
            'type_of_work_id'
        )->leftJoin(Order::TABLE, Order::TABLE . '.id', '=', OrderTypeOfWork::TABLE . '.order_id');
    }

    public function openOrders()
    {
        return $this->orders()
            ->where(function (Builder $q) {
                $q
                    ->whereIn(Order::TABLE . '.status', [OrderStatus::New->value, OrderStatus::In_process->value])
                    ->orWhere(function(Builder $q) {
                        $q->where(Order::TABLE . '.status', OrderStatus::Finished->value)
                            ->where(Order::TABLE . '.status_changed_at', '>', now()->addMinutes(config('orders.bs.do_not_change_finished_status_after') * -1));
                    });
            });
    }

    public function deletedOrders()
    {
        return $this->orders()
            ->whereNotNull(Order::TABLE . '.deleted_at');
    }

    public function typesOfWork(): HasManyThrough
    {
        return $this->hasManyThrough(
            TypeOfWork::class,
            TypeOfWorkInventory::class,
            'inventory_id',
            'id',
            'id',
            'type_of_work_id'
        );
    }

    public function hasRelatedOpenOrders(): bool
    {
        return $this->openOrders()->exists();
    }

    public function hasRelatedDeletedOrders(): bool
    {
        return $this->deletedOrders()->exists();
    }

    public function hasRelatedTypesOfWork(): bool
    {
        return $this->typesOfWork()->exists();
    }

    public function hasRelatedEntities(): bool
    {
        return $this->hasRelatedTypesOfWork()
            || $this->hasRelatedOpenOrders()
            || $this->hasRelatedDeletedOrders();
    }

    public function isInStock(): bool
    {
        return $this->quantity > 0;
    }
}
