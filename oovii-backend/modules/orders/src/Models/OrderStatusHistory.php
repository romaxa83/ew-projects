<?php

namespace WezomCms\Orders\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Carbon;

/**
 * \WezomCms\Orders\Models\OrderStatusHistory
 *
 * @property int $id
 * @property int $order_id
 * @property int $status_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class OrderStatusHistory extends Pivot
{
    public const TABLE = 'order_status_history';

    protected $table = self::TABLE;

    protected $dateFormat = 'Y-m-d H:i:s.u';
}
