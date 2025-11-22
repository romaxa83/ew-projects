<?php

namespace App\Models\Commercial;

use App\Casts\PriceCast;
use App\Contracts\Media\HasMedia;
use App\Enums\Commercial\CommercialQuoteStatusEnum;
use App\Filters\Commercial\CommercialQuoteFilter;
use App\Models\BaseModel;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use App\Traits\Model\Media\InteractsWithMedia;
use App\Traits\Model\SetSortAfterCreate;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

/**
 * @property integer id
 * @property integer commercial_project_id
 * @property string email
 * @property string status                      // default = pending
 * @property int sort
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property Carbon|null closed_at
 * @property boolean send_detail_data           // отправлять в письме все данные или только часть (default = true)
 * @property int count_email_sending            // кол-во отправленных email (default = 0)
 * @property string|null shipping_address
 * @property float shipping_price
 * @property float|null discount_percent         // скидка в виде процентов
 * @property float|null discount_sum             // скидка в виде фиксированной суммы
 * @property float tax
 *
 * @property-read CommercialProject commercialProject
 * @property-read Collection|QuoteItem[] items
 * @property-read Collection|QuoteHistory[] histories
 */
class CommercialQuote extends BaseModel implements HasMedia
{
    use HasFactory;
    use Filterable;
    use InteractsWithMedia;
    use SetSortAfterCreate;

    public const TABLE = 'commercial_quotes';
    protected $table = self::TABLE;

    public const MEDIA_COLLECTION_NAME = 'quotes';
    public const MORPH_NAME = 'quote';

    public const DEFAULT_STATUS = CommercialQuoteStatusEnum::PENDING;

    protected $fillable = [
        'sort',
    ];

    protected $casts = [
        'send_detail_data' => 'boolean',
        'tax' => PriceCast::class,
        'discount_percent' => PriceCast::class,
        'discount_sum' => PriceCast::class,
        'shipping_price' => PriceCast::class,
    ];

    protected $dates = [
        'closed_at',
    ];

    protected $appends = [
        'sub_total',
        'discount',
        'tax_sum',
        'total',
    ];

    public function isPending(): bool
    {
        return $this->status === CommercialQuoteStatusEnum::PENDING;
    }

    public function isDone(): bool
    {
        return $this->status === CommercialQuoteStatusEnum::DONE;
    }

    public function isFinal(): bool
    {
        return $this->status === CommercialQuoteStatusEnum::FINAL;
    }

    public function modelFilter(): string
    {
        return CommercialQuoteFilter::class;
    }

    public function getMediaCollectionName(): string
    {
        return self::MEDIA_COLLECTION_NAME;
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::MEDIA_COLLECTION_NAME)
            ->acceptsMimeTypes(array_merge(
                $this->mimePdf(),
                $this->mimeExcel()
            ))
            ->singleFile();
    }
    // relations
    public function commercialProject(): BelongsTo|CommercialProject
    {
        return $this->belongsTo(CommercialProject::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuoteItem::class, 'commercial_quote_id', 'id');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(QuoteHistory::class, 'quote_id', 'id')->orderByDesc('position');
    }

    // compute attribute
    public function getEstimateAttribute(): string
    {
        return '#' . (string)(100 + $this->id);
    }

    public function getSubTotalAttribute(): float
    {
        return $this->items->sum(function(QuoteItem $model){
            return pretty_price($model->total);
        });
    }

    public function getDiscountAttribute(): float
    {
        if($this->discount_sum){
            return $this->discount_sum;
        }
        if($this->discount_percent){
            return  pretty_price(($this->sub_total / 100) * $this->discount_percent);
        }

        return 0;
    }

    public function getTaxSumAttribute(): float
    {
        if($this->tax){
            return pretty_price((($this->sub_total - $this->discount) * $this->tax)/100);
        }

        return 0;
    }

    //TOTAL =  SUBTOTAL - DISCOUNT + TAX + SHIPPING
    public function getTotalAttribute(): float
    {
        $total = $this->sub_total - $this->discount + $this->tax_sum + $this->shipping_price;
        return  pretty_price($total);
    }

    // Предпросмотр
    public function getPdfStoragePreviewPath($timestamp): string
    {
        Storage::makeDirectory("pdf-preview");

        return storage_path("app/public/pdf-preview/Preview-{$this->id}-{$timestamp}.pdf");
    }

    public function getPreviewUrl($timestamp): string
    {
        return url("storage/pdf-preview/Preview-{$this->id}-{$timestamp}.pdf");
    }
}
