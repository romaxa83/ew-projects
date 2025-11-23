<?php

namespace App\Models\Order;

use Illuminate\Database\Eloquent\Model;
use App\Models\{DispatchEmployer, DispatchTruck, Order, WorkTypes};
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * App\Models\Order\WorkDispatchTouch
 *
 * @property int $id
 * @property int $order_id
 * @property string|null $start_date
 * @property string|null $start_time
 * @property string|null $start_time_to
 * @property string|null $duration
 * @property int|null $trucks
 * @property int|null $employees
 * @property string|null $notes
 * @property int|null $notes_by
 * @property \Illuminate\Support\Carbon|null $notes_created_at
 * @property \Illuminate\Support\Carbon $created_at
 * @property string $updated_at
 * @property int $in_dispatch Разрешен Dispatch
 * @property \Illuminate\Support\Carbon|null $dispatch_updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|DispatchEmployer[] $dispatchEmployees
 * @property-read int|null $dispatch_employees_count
 * @property-read \Illuminate\Database\Eloquent\Collection|DispatchTruck[] $dispatchTrucks
 * @property-read int|null $dispatch_trucks_count
 * @property-read Order|null $order
 * @property-read WorkTypes[]|BelongsToMany types
 * @method static \Illuminate\Database\Eloquent\Builder|WorkDispatchTouch newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WorkDispatchTouch newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WorkDispatchTouch query()
 * @method static \Illuminate\Database\Eloquent\Builder|WorkDispatchTouch whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WorkDispatchTouch whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WorkDispatchTouch whereDispatchUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WorkDispatchTouch whereDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WorkDispatchTouch whereEmployees($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WorkDispatchTouch whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WorkDispatchTouch whereInDispatch($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WorkDispatchTouch whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WorkDispatchTouch whereNotesBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WorkDispatchTouch whereNotesCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WorkDispatchTouch whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WorkDispatchTouch whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WorkDispatchTouch whereStartTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WorkDispatchTouch whereStartTimeTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WorkDispatchTouch whereTrucks($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WorkDispatchTouch whereUpdatedAt($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read int|null $audits_count
 * @property-read \Illuminate\Database\Eloquent\Collection|WorkTypes[] $types
 * @property-read int|null $types_count
 * @mixin \Eloquent
 */
class WorkDispatchTouch extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'orders_works';
    protected $dates = ['deleted_at', 'notes_created_at'];
    protected $fillable = ['start_date', 'duration'];

    public const UPDATED_AT = 'dispatch_updated_at';

    public function dispatchTrucks()
    {
        return $this->hasMany(DispatchTruck::class, 'work_id', 'id');
    }

    public function order()
    {
        return $this->hasOne(Order::class, 'id', 'order_id');
    }

    public function dispatchEmployees()
    {
        return $this->hasMany(DispatchEmployer::class, 'work_id', 'id');
    }

    public function types(): BelongsToMany
    {
        return $this->belongsToMany(
            WorkTypes::class,
            'orders_works_2_work',
            'work_id',
            'work_type_id'
        );
    }

    public function worksNameAsSrt(): string
    {
        $tmp = $this->types->pluck('title')->toArray();

        return implode(', ', $tmp);
    }
}
