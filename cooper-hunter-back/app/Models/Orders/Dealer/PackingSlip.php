<?php

namespace App\Models\Orders\Dealer;

use App\Casts\PriceCast;
use App\Contracts\Media\HasMedia;
use App\Contracts\Utilities\Dispatchable;
use App\Enums\Orders\Dealer\OrderStatus;
use App\Models\BaseModel;
use App\Traits\HasFactory;
use App\Traits\Model\Media\InteractsWithMedia;
use Barryvdh\LaravelIdeHelper\Eloquent;
use Database\Factories\Orders\Dealer\PackingSlipFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * @property int id
 * @property string guid
 * @property int order_id
 * @property OrderStatus status
 * @property string number
 * @property string|null tracking_number
 * @property string|null tracking_company
 * @property float tax                          // налог, пиходит от 1с
 * @property float shipping_price               // стоимость доставки, пиходит от 1с
 * @property float total                        // общая сумма заказа, пиходит от 1с
 * @property float total_discount               // общую сумма скидки, пиходит от 1с
 * @property float total_with_discount          // общая сумма заказа со скидкой, пиходит от 1с
 * @property string|null invoice
 * @property Carbon|null invoice_at
 * @property Carbon|null shipped_at
 * @property Carbon|null created_at
 * @property Carbon|null updated_at
 * @property array|null files                   // массив файлов (название и линк), который пришли от 1с
 *
 * @see PackingSlip::order()
 * @property-read Order order
 *
 * @see PackingSlip::items()
 * @property-read PackingSlipItem|Collection items
 *
 * @see PackingSlip::serialNumbers()
 * @property-read PackingSlipSerialNumber|Collection serialNumbers
 *
 * @see PackingSlip::dimensions()
 * @property-read Dimensions|Collection dimensions
 *
 * @see PackingSlip::getItemsQtyAttribute()
 * @property-read int items_qty
 *
 * @method static PackingSlipFactory factory(...$parameters)
 *
 * @mixin Eloquent
 */
class PackingSlip extends BaseModel implements
    HasMedia,
    Dispatchable
{
    use HasFactory;
    use InteractsWithMedia;

    public const TABLE = 'dealer_order_packing_slips';
    protected $table = self::TABLE;

    public const MEDIA_COLLECTION_NAME = 'packing_slip';
    public const MORPH_NAME = 'packing_slip';

    public const PDF_FILE_GENERATE_DIR = 'dealer-order-pdf';
    public const EXCEL_FILE_PREFIX = 'packing-slip';

    protected $dates = [
        'shipped_at',
        'invoice_at'
    ];

    protected $casts = [
        'status' => OrderStatus::class,
        'tax' => PriceCast::class,
        'shipping_price' => PriceCast::class,
        'total' => PriceCast::class,
        'total_discount' => PriceCast::class,
        'total_with_discount' => PriceCast::class,
        'files' => 'array',
    ];

    public function getMediaCollectionName(): string
    {
        return self::MEDIA_COLLECTION_NAME;
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::MEDIA_COLLECTION_NAME)
            ->acceptsMimeTypes(array_merge(
                $this->mimeImage(),
                $this->mimePdf(),
                $this->mimeExcel(),
            ));
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function dimensions(): hasMany
    {
        return $this->hasMany(Dimensions::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PackingSlipItem::class);
    }

    public function serialNumbers(): HasMany
    {
        return $this->hasMany(PackingSlipSerialNumber::class);
    }

    public function getItemsQtyAttribute(): float
    {
        $this->load('items');
        return $this->items->sum(function(PackingSlipItem $model){
            return $model->qty;
        });
    }

    public function getInvoiceFileStoragePath(): string
    {
        Storage::makeDirectory(self::PDF_FILE_GENERATE_DIR);

        return storage_path("app/public/" . self::PDF_FILE_GENERATE_DIR . "/" . $this->invoiceFileName());
    }

    public function getInvoiceFileStorageUrl(): ?string
    {
        if(!file_exists($this->getInvoiceFileStoragePath())){
            return null;
        }
        return url("storage/" . self::PDF_FILE_GENERATE_DIR . "/" . $this->invoiceFileName());
    }

    public function invoiceFileName(): string
    {
        return "Order-{$this->order_id}-packing-slim-{$this->id}-invoice.pdf";
    }

    public function getPdfFileStoragePath(): string
    {
        Storage::makeDirectory(self::PDF_FILE_GENERATE_DIR);

        return storage_path("app/public/" . self::PDF_FILE_GENERATE_DIR . "/" . $this->pdfFileName());
    }

    public function getPdfFileStorageUrl(): ?string
    {
        if(!file_exists($this->getPdfFileStoragePath())){
            return null;
        }
        return url("storage/" . self::PDF_FILE_GENERATE_DIR . "/" . $this->pdfFileName());
    }

    public function pdfFileName(): string
    {
        return "Order-{$this->order_id}-packing-slim-{$this->id}-pdf.pdf";
    }
}
