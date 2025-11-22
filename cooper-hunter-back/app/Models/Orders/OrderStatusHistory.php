<?php

namespace App\Models\Orders;

use App\Enums\Orders\OrderStatusEnum;
use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * @property int id
 * @property int order_id
 * @property string status
 * @property string changer_type
 * @property int changer_id
 * @property Carbon|null created_at
 * @property Carbon|null updated_at
 */
class OrderStatusHistory extends BaseModel
{
    public const TABLE = 'order_status_histories';

    public const MORPH_NAME = 'order_status_history';

    protected $fillable = [
        'order_id',
        'status',
        'changer_type',
        'changer_id',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'order_id' => 'int',
        'status' => OrderStatusEnum::class,
        'member_id' => 'int',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function changer(): MorphTo
    {
        return $this->morphTo();
    }


}
