<?php

namespace App\Models\Order;

use Database\Factories\Orders\StatusHistoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Order\StatusChangeHistory
 *
 * @property int $id
 * @property int $order_id
 * @property int $user_id
 * @property int $prev_status
 * @property int $new_status
 * @property string $created_at
 * @property int $is_deleted
 * @method static \Illuminate\Database\Eloquent\Builder|StatusChangeHistory latestNotDeleted()
 * @method static \Illuminate\Database\Eloquent\Builder|StatusChangeHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StatusChangeHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StatusChangeHistory query()
 * @method static \Illuminate\Database\Eloquent\Builder|StatusChangeHistory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StatusChangeHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StatusChangeHistory whereIsDeleted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StatusChangeHistory whereNewStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StatusChangeHistory whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StatusChangeHistory wherePrevStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StatusChangeHistory whereUserId($value)
 * @method static StatusHistoryFactory factory(...$parameters)
 * @mixin \Eloquent
 */
class StatusChangeHistory extends Model
{
    use HasFactory;

    public const TABLE = 'orders_status_change_history';
    protected $table = self::TABLE;

    public $timestamps = false;

    protected $fillable = [
        'prev_status',
        'new_status',
        'user_id',
        'created_at',
        'is_deleted'
    ];

    protected static function newFactory(): StatusHistoryFactory
    {
        return StatusHistoryFactory::new();
    }

    public function scopeLatestNotDeleted($q)
    {
        return $q->where('is_deleted', 0)->orderBy('id', 'desc');
    }
}
