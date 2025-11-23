<?php

namespace App\Models\Order;

use App\Helpers\DbConnections;
use Database\Factories\Orders\CustomExtraFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * App\Models\Order\CustomExtra
 *
 * @property int $id
 * @property int $order_id
 * @property string $title
 * @property string $price
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|CustomExtra newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CustomExtra newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CustomExtra query()
 * @method static \Illuminate\Database\Eloquent\Builder|CustomExtra whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomExtra whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomExtra whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomExtra wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomExtra whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomExtra whereUpdatedAt($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read int|null $audits_count
 * @mixin \Eloquent
 * @method static CustomExtraFactory factory(...$parameters)
 */
class CustomExtra extends Model implements Auditable
{
    use AuditableTrait;
    use HasFactory;

    public const MORPH_NAME = 'order-material-custom';

    public const TABLE = 'orders_customs_extras';
    protected $table = self::TABLE;

    protected $connection = DbConnections::DEFAULT;

    protected $fillable = [
        'title',
        'price'
    ];

    protected static function newFactory(): CustomExtraFactory
    {
        return CustomExtraFactory::new();
    }
}
