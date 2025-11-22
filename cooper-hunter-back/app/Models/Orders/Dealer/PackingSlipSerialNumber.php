<?php

namespace App\Models\Orders\Dealer;

use App\Models\BaseModel;
use App\Models\Catalog\Products\Product;
use App\Traits\HasFactory;
use Barryvdh\LaravelIdeHelper\Eloquent;
use Database\Factories\Orders\Dealer\PackingSlipSerialNumberFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int id
 * @property int packing_slip_id
 * @property int product_id
 * @property string serial_number
 *
 * @see PackingSlipSerialNumber::product()
 * @property-read Product product
 *
 * @see PackingSlipSerialNumber::packingSlip()
 * @property-read PackingSlip packingSlip
 *
 * @method static PackingSlipSerialNumberFactory factory(...$parameters)
 *
 * @mixin Eloquent
 */
class PackingSlipSerialNumber extends BaseModel
{
    use HasFactory;

    public $timestamps = false;

    public const TABLE = 'dealer_order_packing_slip_serial_numbers';
    protected $table = self::TABLE;

    protected $fillable = [];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function packingSlip(): BelongsTo
    {
        return $this->belongsTo(PackingSlip::class);
    }
}
