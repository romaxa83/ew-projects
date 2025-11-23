<?php

namespace App\Models\Order;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * App\Models\Order\Extended
 *
 * @property int $order_id
 * @property int|null $ext_id
 * @property array|null $import JSON
 * @property array|null $miscs JSON
 * @method static \Illuminate\Database\Eloquent\Builder|Extended newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Extended newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Extended query()
 * @method static \Illuminate\Database\Eloquent\Builder|Extended whereExtId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Extended whereImport($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Extended whereMiscs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Extended whereOrderId($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read int|null $audits_count
 * @mixin \Eloquent
 */
class Extended extends Model implements Auditable
{
    use AuditableTrait;

    public const MORPH_NAME = 'order-extended';

    public const TABLE = 'orders_extended';
    protected $table = self::TABLE;
    protected $primaryKey = 'order_id';

    public $timestamps = false;

    protected $fillable = [
        'ext_id',
        'import',
        'miscs'
    ];

    protected $casts = [
        'miscs' => 'array',
        'import' => 'array',
    ];

}
