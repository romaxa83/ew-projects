<?php

namespace App\Models\Order;

use Illuminate\Database\Eloquent\{Builder,
    Factories\HasFactory,
    Relations\HasMany,
    Relations\HasOne,
    SoftDeletes,
    Model};
use Database\Factories\Orders\StatusFactory;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;


/**
 * App\Models\Order\Status
 *
 * @property int $id
 * @property string $title
 * @property string|null $color
 * @property int $group_id
 * @property int|null $sort
 * @property array|null $actions
 * @property int|null $in_calendar
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read int|null $audits_count
 * @property-read bool $disable_dispatch
 * @property-read bool $enable_dispatch
 * @property \App\Models\Order\StatusGroup|null $group
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Order\StatusRoute[] $routed
 * @property-read int|null $routed_count
 * @method static Builder|Status newModelQuery()
 * @method static Builder|Status newQuery()
 * @method static \Illuminate\Database\Query\Builder|Status onlyTrashed()
 * @method static Builder|Status query()
 * @method static Builder|Status routedList()
 * @method static Builder|Status selected()
 * @method static Builder|Status whereActions($value)
 * @method static Builder|Status whereColor($value)
 * @method static Builder|Status whereDeletedAt($value)
 * @method static Builder|Status whereGroupId($value)
 * @method static Builder|Status whereId($value)
 * @method static Builder|Status whereInCalendar($value)
 * @method static Builder|Status whereSort($value)
 * @method static Builder|Status whereTitle($value)
 * @method static \Illuminate\Database\Query\Builder|Status withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Status withoutTrashed()
 * @method static StatusFactory factory(...$parameters)
 * @mixin \Eloquent
 */
class Status extends Model implements Auditable
{
    use AuditableTrait;
    use SoftDeletes;
    use HasFactory;

    public const NEW_LEAD_ID = 1;
    public const CALCULATED_DONE_ID = 4;
    public const BOOKED_ID = 5;
    public const LOST_ID = 9;
    public const SUCCESS_ID = 10;
    public const DUPLICATE_ID = 12;
    public const SALES_DONE_ID = 14;
    public const CANCELED_ID = 25;

    public const TABLE = 'orders_statuses';
    protected $table = self::TABLE;

    public $timestamps = false;

    protected $dates = [
        'deleted_at'
    ];

    protected $fillable = [
        'title',
        'color',
        'group',
        'sort',
        'actions'
    ];

    protected $casts = [
        'actions' => 'json',
    ];
    protected $appends = [
        'enable_dispatch',
        'disable_dispatch',
        'can_edit'
    ];

    protected static function newFactory(): StatusFactory
    {
        return StatusFactory::new();
    }

    public function group(): HasOne
    {
        return $this->hasOne(StatusGroup::class, 'id', 'group_id');
    }

    public function routed(): HasMany
    {
        return $this->hasMany(StatusRoute::class);
    }

    public function setGroupAttribute($value): void
    {
        $this->attributes['group_id'] = $value;
    }

    /**
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeRoutedList(Builder $query): Builder
    {
        return $query
            ->with([
                'routed' => function ($q) {
                    $q->join('orders_statuses', 'orders_statuses.id', '=', 'orders_statuses_routes.route_to_status_id')
                        ->orderBy('orders_statuses.sort', 'asc')
                        ->orderBy('orders_statuses.title', 'asc');
                }
            ]);
    }

    /**
     * Активные записи.
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeSelected(Builder $query): Builder
    {
        return $query
            ->with('group:id,title,sort')
            ->orderBy('sort');
    }

    public function getEnableDispatchAttribute(): bool
    {
        return in_array('enable_dispatch', (array) $this->actions, true);
    }

    public function getDisableDispatchAttribute(): bool
    {
        return in_array('disable_dispatch', (array) $this->actions, true);
    }

    public function getCanEditAttribute(): bool
    {
        if($this->id == self::CANCELED_ID){
            return false;
        }

        return true;
    }
}
