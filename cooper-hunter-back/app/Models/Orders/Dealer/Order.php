<?php

namespace App\Models\Orders\Dealer;

use App\Casts\PriceCast;
use App\Contracts\Alerts\AlertModel;
use App\Contracts\Media\HasMedia;
use App\Contracts\Roles\HasGuardUser;
use App\Contracts\Utilities\Dispatchable;
use App\Enums\Orders\Dealer\DeliveryType;
use App\Enums\Orders\Dealer\OrderStatus;
use App\Enums\Orders\Dealer\OrderType;
use App\Enums\Orders\Dealer\PaymentType;
use App\Filters\Orders\Dealer\OrderFilter;
use App\Models\BaseModel;
use App\Models\Companies\ShippingAddress;
use App\Models\Dealers\Dealer;
use App\Models\Payments\PaymentCard;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use App\Traits\Model\Media\InteractsWithMedia;
use Database\Factories\Orders\Dealer\OrderFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * @property int id
 * @property string|null guid
 * @property int dealer_id
 * @property int|null shipping_address_id
 * @property int|null payment_card_id
 * @property OrderType type
 * @property OrderStatus status
 * @property DeliveryType delivery_type
 * @property PaymentType payment_type
 * @property string|null po
 * @property string|null tracking_number    // del
 * @property string|null tracking_company   // del
 * @property string|null terms
 * @property string|null comment
 * @property Carbon|null shipped_at             // дата доставки, пиходит от 1с
 * @property Carbon|null deleted_at
 * @property Carbon|null created_at
 * @property Carbon|null updated_at
 * @property Carbon|null invoice_at
 * @property Carbon|null approved_at            // дата когда заявка была потверждена 1с
 * @property array|null files                   // массив файлов (название и линк), который пришли от 1с
 * @property float tax                          // налог, пиходит от 1с
 * @property float shipping_price               // стоимость доставки, пиходит от 1с
 * @property float total                        // общая сумма заказа, пиходит от 1с
 * @property float total_discount               // общую сумма скидки, пиходит от 1с
 * @property float total_with_discount          // общая сумма заказа со скидкой, пиходит от 1с
 * @property string|null invoice                // № инвойса, пиходит от 1с, если инвойс нужно формировать для заказа
 * @property string|null error                  // ошибка по заявке, которая может прийти от 1с, на предмет превышения лимита или др. причины
 * @property bool has_invoice                   // поле указывает есть ли инвойс для заказ, т.е. формируеться ли он для заказа или только для слипов (default=false)
 * @property string|null hash                   // хеш данных (которые приходят от 1с), чтоб понимать изменились данные или нет, и соответсвенно отправлять или нет email дилеру
 *
 * @see Order::shippingAddress()
 * @property-read ShippingAddress|null shippingAddress
 *
 * @see Order::paymentCard()
 * @property-read PaymentCard|null paymentCard
 *
 * @see Order::dealer()
 * @property-read Dealer dealer
 *
 * @see Order::items()
 * @property-read Item|Collection items
 *
 * @see Order::serialNumbers()
 * @property-read SerialNumber|Collection serialNumbers
 *
 * @see Order::packingSlips()
 * @property-read PackingSlip|Collection packingSlips
 *
 * @see Order::getTotalAmountAttribute()
 * @property-read float total_amount
 *
 * @see Order::getTermAttribute()
 * @property-read string|null term
 *
 * @see Order::getItemsQtyAttribute()
 * @property-read int items_qty
 *
 * @method static OrderFactory factory(...$parameters)
 */
class Order extends BaseModel implements AlertModel, HasMedia, Dispatchable
{
    use HasFactory;
    use SoftDeletes;
    use Filterable;
    use InteractsWithMedia;

    public const MEDIA_COLLECTION_NAME = 'dealer-orders';
    public const MORPH_NAME = 'dealer-order';

    public const PDF_FILE_GENERATE_DIR = 'dealer-order-pdf';
    public const ORDER_FILES_FOLDER = 'dealer-order';

    public const TABLE = 'dealer_orders';
    protected $table = self::TABLE;

    protected $fillable = [
        'guid',
        'error',
        'status',
        'hash',
    ];

    protected $dates = [
        'shipped_at',
        'invoice_at',
        'approved_at',
    ];

    protected $casts = [
        'status' => OrderStatus::class,
        'type' => OrderType::class,
        'delivery_type' => DeliveryType::class,
        'payment_type' => PaymentType::class,
        'terms' => 'array',
        'files' => 'array',
        'tax' => PriceCast::class,
        'shipping_price' => PriceCast::class,
        'total' => PriceCast::class,
        'total_discount' => PriceCast::class,
        'total_with_discount' => PriceCast::class,
        'has_invoice' => 'boolean',
    ];

    public function getMediaCollectionName(): string
    {
        return self::MEDIA_COLLECTION_NAME;
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::MEDIA_COLLECTION_NAME)
            ->acceptsMimeTypes(array_merge(
                $this->mimeExcel(),
                $this->mimePdf(),
                $this->mimeImage(),
                $this->mimeWord()
            ));
    }

    public function modelFilter(): string
    {
        return OrderFilter::class;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getMorphType(): string
    {
        return self::MORPH_NAME;
    }

    public function shippingAddress(): BelongsTo
    {
        return $this->belongsTo(ShippingAddress::class);
    }

    public function paymentCard(): BelongsTo
    {
        return $this->belongsTo(PaymentCard::class);
    }

    public function dealer(): BelongsTo
    {
        return $this->belongsTo(Dealer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    // todo deprecated
    public function serialNumbers(): HasMany
    {
        return $this->hasMany(SerialNumber::class);
    }

    public function packingSlips(): HasMany
    {
        return $this->hasMany(PackingSlip::class);
    }

    public function getTotalAmountAttribute(): float
    {
        $this->load('items');

        $tmp = [
            'total' => 0,
            'discount' => 0,
        ];

        $this->items->each(function(Item $model) use (&$tmp){
            $tmp['total'] += $model->amount;
            $tmp['discount'] += $model->discount;
            return $tmp;
        });

        return pretty_price($tmp['total'] - $tmp['discount']);
    }

    public function getItemsQtyAttribute(): float
    {
        $this->load('items');
        return $this->items->sum(function(Item $model){
            return $model->qty;
        });
    }

    public function getTermAttribute(): ?string
    {
        return data_get($this->terms, 'name', $this->terms);
    }

    public function isOwner(HasGuardUser $user = null): bool
    {
        if($user){
            return $user instanceof Dealer && $this->dealer_id === $user->id;
        }

        return false;
    }

    public function getEstimateStoragePath(): string
    {
        Storage::makeDirectory(self::PDF_FILE_GENERATE_DIR);

        return storage_path("app/public/".self::PDF_FILE_GENERATE_DIR."/Order-estimate-{$this->id}.pdf");
    }

    public function getEstimateStorageUrl(): string
    {
        return url("storage/".self::PDF_FILE_GENERATE_DIR."/Order-estimate-{$this->id}.pdf");
    }

    public function getInvoiceStoragePath(): string
    {
        Storage::makeDirectory(self::PDF_FILE_GENERATE_DIR);

        return storage_path("app/public/".self::PDF_FILE_GENERATE_DIR."/Order-invoice-{$this->id}.pdf");
    }

    public function getInvoiceStorageUrl(): string
    {
        return url("storage/".self::PDF_FILE_GENERATE_DIR."/Order-invoice-{$this->id}.pdf");
    }

    public function equalsHash(string $hash): bool
    {
        return $this->hash === $hash;
    }
}
