<?php

namespace App\Models;

use App\Helpers\DbConnections;
use App\Models\Order\WorkDispatchTouch;
use App\Models\Truck\Truck;
use Database\Factories\Trucks\DispatchTruckFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * App\Models\DispatchTruck
 *
 * @property int $id
 * @property int $work_id
 * @property int $truck_id
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \App\Models\Truck\Truck|null $truck
 * @property-read WorkDispatchTouch $work
 * @method static \Illuminate\Database\Eloquent\Builder|DispatchTruck newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DispatchTruck newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DispatchTruck query()
 * @method static \Illuminate\Database\Eloquent\Builder|DispatchTruck whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DispatchTruck whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DispatchTruck whereTruckId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DispatchTruck whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DispatchTruck whereWorkId($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read int|null $audits_count
 * @mixin \Eloquent
 * @method static DispatchTruckFactory factory(...$parameters)
 */
class DispatchTruck extends Model implements Auditable
{
    use AuditableTrait;
    use HasFactory;

    public const MORPH_NAME = 'dispatch-truck';

    public const TABLE = 'dispatch_trucks';
    protected $table = self::TABLE;

    protected $connection = DbConnections::DEFAULT;

    protected $fillable = [
        'truck_id'
    ];

    protected $touches = ['work'];

    protected static function newFactory(): DispatchTruckFactory
    {
        return DispatchTruckFactory::new();
    }

    public function work()
    {
        return $this->belongsTo(WorkDispatchTouch::class);
    }

    public function truck()
    {
        return $this->hasOne(Truck::class, 'id', 'truck_id');
    }
}
