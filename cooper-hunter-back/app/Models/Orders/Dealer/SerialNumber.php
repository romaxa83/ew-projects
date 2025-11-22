<?php

namespace App\Models\Orders\Dealer;

use App\Models\BaseModel;
use App\Models\Catalog\Products\Product;
use App\Traits\HasFactory;
use Barryvdh\LaravelIdeHelper\Eloquent;
use Database\Factories\Orders\Dealer\SerialNumberFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int id
 * @property int order_id
 * @property int product_id
 * @property string serial_number
 *
 * @see SerialNumber::product()
 * @property-read Product product
 *
 * @see SerialNumber::order()
 * @property-read Order order
 *
 * @method static SerialNumberFactory factory(...$parameters)
 *
 * @mixin Eloquent
 */

// todo deprecated
class SerialNumber extends BaseModel
{
    use HasFactory;

    public $timestamps = false;

    public const TABLE = 'dealer_order_serial_numbers';
    protected $table = self::TABLE;

    protected $fillable = [];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
