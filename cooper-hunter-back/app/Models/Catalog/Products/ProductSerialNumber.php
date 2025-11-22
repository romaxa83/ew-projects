<?php

namespace App\Models\Catalog\Products;

use App\Models\BaseModel;
use App\Traits\HasFactory;
use App\Traits\SimpleEloquent;
use Database\Factories\Catalog\Products\ProductSerialNumberFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int product_id
 * @property string serial_number
 *
 * @see ProductSerialNumber::product()
 * @property-read Product product
 *
 * @method static ProductSerialNumberFactory factory(...$options)
 */
class ProductSerialNumber extends BaseModel
{
    use HasFactory;
    use SimpleEloquent;

    public const TABLE = 'product_serial_numbers';

    public $timestamps = false;

    public $incrementing = false;

    protected $table = self::TABLE;

    protected $primaryKey = 'serial_number';

    protected $fillable = [
        'product_id',
        'serial_number'
    ];

    public function product(): BelongsTo|Product
    {
        return $this->belongsTo(Product::class);
    }
}
