<?php

namespace App\Models\Order;

use Illuminate\Database\Eloquent\{Model, SoftDeletes};
use OwenIt\Auditing\Contracts\Auditable;

/**
 * App\Models\Order\StatusRoute
 *
 * @property int $id
 * @property int|null $status_id
 * @property int|null $route_to_status_id
 * @property int $sort
 * @property \App\Models\Order\Status|null $status_from
 * @property \App\Models\Order\Status|null $status_to
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read int|null $audits_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @method static \Illuminate\Database\Eloquent\Builder|StatusRoute newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StatusRoute newQuery()
 * @method static \Illuminate\Database\Query\Builder|StatusRoute onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|StatusRoute query()
 * @method static \Illuminate\Database\Eloquent\Builder|StatusRoute whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StatusRoute whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StatusRoute whereRouteToStatusId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StatusRoute whereStatusId($value)
 * @method static \Illuminate\Database\Query\Builder|StatusRoute withTrashed()
 * @method static \Illuminate\Database\Query\Builder|StatusRoute withoutTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|StatusRoute order()
 * @method static \Illuminate\Database\Eloquent\Builder|StatusRoute whereSort($value)
 * @mixin \Eloquent
 */
class StatusRoute extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;

    protected $table = 'orders_statuses_routes';
    protected $dates = ['deleted_at'];
    protected $fillable = ['status_from', 'status_to', 'sort'];
    public $timestamps = false;

    public function scopeOrder($q)
    {
        return $q
            ->orderBy('status_id')
            ->orderBy('sort');
    }

    public function status_from()
    {
        return $this->HasOne(Status::class, 'id', 'status_id');
    }

    public function status_to()
    {
        return $this->HasOne(Status::class, 'id', 'route_to_status_id');
    }

    public function setStatusFromAttribute($value): void
    {
        $this->attributes['status_id'] = (int) $value;
    }

    public function setStatusToAttribute($value): void
    {
        $this->attributes['route_to_status_id'] = (int) $value;
    }

}
