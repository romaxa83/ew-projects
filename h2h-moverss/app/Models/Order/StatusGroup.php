<?php

namespace App\Models\Order;

use Database\Factories\Orders\StatusGroupFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

/**
 * App\Models\Order\StatusGroup
 *
 * @property int $id
 * @property string $title
 * @property int $sort
 * @property int|null $in_report
 * @property int|null in_funel_report
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Order\Status[] $statuses
 * @property-read int|null $statuses_count
 * @method static \Illuminate\Database\Eloquent\Builder|StatusGroup newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StatusGroup newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StatusGroup query()
 * @method static \Illuminate\Database\Eloquent\Builder|StatusGroup whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StatusGroup whereInReport($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StatusGroup whereSort($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StatusGroup whereTitle($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read int|null $audits_count
 * @method static StatusGroupFactory factory(...$parameters)
 * @mixin \Eloquent
 */
class StatusGroup extends Model implements Auditable
{
    use AuditableTrait;
    use HasFactory;

    public const TABLE = 'orders_statuses_groups';
    protected $table = self::TABLE;

    public $timestamps = false;

    protected $fillable = [
        'title',
        'sort',
        'in_report',
        'in_funel_report'
    ];

    protected static function newFactory(): StatusGroupFactory
    {
        return StatusGroupFactory::new();
    }

    public function statuses(): HasMany
    {
        return $this->hasMany(Status::class, 'group_id', 'id');
    }
}
