<?php

namespace App\Models\Order;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * App\Models\Order\Worker
 *
 * @property int $id
 * @property int $order_id
 * @property int $user_id
 * @property int $is_approved
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Worker newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Worker newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Worker query()
 * @method static \Illuminate\Database\Eloquent\Builder|Worker whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Worker whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Worker whereIsApproved($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Worker whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Worker whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Worker whereUserId($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read int|null $audits_count
 * @mixin \Eloquent
 */
class Worker extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'orders_workers';
}
