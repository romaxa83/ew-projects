<?php

namespace App\Models\Dealers;

use App\Models\BasePivot;
use App\Models\Companies\ShippingAddress;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int dealer_id
 * @property int shipping_address_id
 */
class DealerShippingAddressPivot extends BasePivot
{
    public $timestamps = false;

    public const TABLE = 'dealer_shipping_address_pivot';
    protected $table = self::TABLE;


    protected $fillable = [
        'shipping_address_id',
        'dealer_id',
    ];

    public function shippingAddress(): BelongsTo
    {
        return $this->belongsTo(ShippingAddress::class);
    }

    public function dealer(): BelongsTo
    {
        return $this->belongsTo(Dealer::class);
    }
}


