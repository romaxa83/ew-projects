<?php

namespace App\Models\Orders;

use App\Casts\PriceCast;
use App\Models\BaseModel;
use App\Models\Orders\Categories\OrderCategory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int order_id
 * @property int order_category_id
 * @property int quantity
 * @property string description
 * @property int price
 */
class OrderPart extends BaseModel
{
    public const TABLE = 'order_parts';

    public $incrementing = false;

    public $timestamps = false;

    protected $primaryKey = 'order_part_id';

    protected $fillable = [
        'order_id',
        'order_category_id',
        'quantity',
        'description',
        'price'
    ];

    protected $casts = [
        'quantity' => 'int',
        'price' => PriceCast::class
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function orderCategory(): BelongsTo
    {
        return $this->belongsTo(OrderCategory::class);
    }

    public function translation(): Categories\OrderCategoryTranslation
    {
        return $this->orderCategory->translation;
    }
}
