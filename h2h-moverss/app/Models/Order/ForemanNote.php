<?php

namespace App\Models\Order;

use App\Models\Employee;
use App\Models\Order;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\{ Relations\HasOne, Model};

/**
 * @property int id
 * @property int order_id
 * @property int foreman_id
 * @property string text
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Notes newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Notes newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Notes query()
 * @method static \Illuminate\Database\Eloquent\Builder|Notes whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notes whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notes whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notes whereText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notes whereUpdatedAt($value)
 *
 * @see self::order()
 * @property Order|HasOne order
 *
 * @see self::foreman()
 * @property Employee|HasOne foreman
 */
class ForemanNote extends Model implements Auditable
{
    use AuditableTrait;

    public const MORPH_NAME = 'order-foreman-note';

    public const TABLE = 'order_foreman_notes';
    protected $table = self::TABLE;

    protected $dates = [];

    protected $casts = [];

    public function foreman(): HasOne
    {
        return $this->hasOne(Employee::class, 'id', 'foreman_id');
    }

    public function order(): HasOne
    {
        return $this->hasOne(Order::class, 'id', 'order_id');
    }
}
