<?php

namespace App\Models\Orders\Dealer;

use App\Models\BaseModel;
use App\Traits\HasFactory;
use Barryvdh\LaravelIdeHelper\Eloquent;
use Database\Factories\Orders\Dealer\DimensionsFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int id
 * @property int packing_slip_id
 * @property int pallet
 * @property int box_qty
 * @property string type
 * @property float weight
 * @property float width
 * @property float depth
 * @property float height
 * @property int class_freight
 *
 * @method static DimensionsFactory factory(...$parameters)
 *
 * @mixin Eloquent
 */
class Dimensions extends BaseModel
{
    use HasFactory;

    public $timestamps = false;

    public const TABLE = 'dealer_order_packing_slip_dimensions';
    protected $table = self::TABLE;

    public function packageSlip(): BelongsTo
    {
        return $this->belongsTo(PackingSlip::class);
    }
}
