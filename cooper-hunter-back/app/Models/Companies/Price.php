<?php

namespace App\Models\Companies;

use App\Casts\PriceCast;
use App\Models\BaseModel;
use App\Models\Catalog\Products\Product;
use App\Traits\HasFactory;
use Carbon\Carbon;
use Database\Factories\Companies\PriceFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property integer id
 * @property integer company_id
 * @property integer product_id
 * @property float price
 * @property string|null desc
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 * @see Price::company()
 * @property-read Company dealer
 *
 * @see Price::product()
 * @property-read Product product
 *
 * @method static PriceFactory factory(...$options)
 */
class Price extends BaseModel
{
    use HasFactory;

    public const TABLE = 'company_prices';
    protected $table = self::TABLE;

    protected $fillable = [
        'price',
        'desc',
    ];

    protected $casts = [
        'price' => PriceCast::class,
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
