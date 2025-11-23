<?php

namespace App\Models\Order;

use App\Helpers\DbConnections;
use App\Models\Material as MaterialParent;
use Database\Factories\Orders\MaterialFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * App\Models\Order\Material
 *
 * @property int $id
 * @property int|null $type_id
 * @property int $order_id
 * @property int|null $material_id
 * @property string $title
 * @property string $price
 * @property int $qty
 * @property int $need_packing
 * @property int $need_unpacking
 * @property string|null $packing_price
 * @property string|null $unpacking_price
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read MaterialParent|null $material
 * @method static \Illuminate\Database\Eloquent\Builder|Material newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Material newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Material query()
 * @method static \Illuminate\Database\Eloquent\Builder|Material whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Material whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Material whereMaterialId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Material whereNeedPacking($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Material whereNeedUnpacking($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Material whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Material wherePackingPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Material wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Material whereQty($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Material whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Material whereTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Material whereUnpackingPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Material whereUpdatedAt($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read int|null $audits_count
 * @mixin \Eloquent
 * @method static MaterialFactory factory(...$parameters)
 */
class Material extends Model implements Auditable
{
    use AuditableTrait;
    use HasFactory;

    public const MORPH_NAME = 'order-material';

    public const TABLE = 'orders_materials';
    protected $table = self::TABLE;

    protected $connection = DbConnections::DEFAULT;

    protected $fillable = [
        'type_id',
        'order_id',
        'material_id',
        'title',
        'price',
        'qty',
        'need_packing',
        'need_unpacking',
        'packing_price',
        'unpacking_price'
    ];

    protected static function newFactory(): MaterialFactory
    {
        return MaterialFactory::new();
    }

    public function material(): HasOne
    {
        return $this->hasOne(MaterialParent::class, 'id', 'material_id');
    }

}
