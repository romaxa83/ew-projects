<?php

namespace App\Models\Order;

use App\Helpers\DbConnections;
use Database\Factories\Orders\WaypointNotesFactory;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\{Factories\HasFactory, Relations\BelongsTo, Relations\HasOne, SoftDeletes, Model};

/**
 * App\Models\Order\WaypointNotes
 *
 * @property int $id
 * @property int $waypoint_id
 * @property int $user_id
 * @property string $value
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|WaypointNotes newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WaypointNotes newQuery()
 * @method static \Illuminate\Database\Query\Builder|WaypointNotes onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|WaypointNotes query()
 * @method static \Illuminate\Database\Eloquent\Builder|WaypointNotes whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WaypointNotes whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WaypointNotes whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WaypointNotes whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WaypointNotes whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WaypointNotes whereValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WaypointNotes whereWaypointId($value)
 * @method static \Illuminate\Database\Query\Builder|WaypointNotes withTrashed()
 * @method static \Illuminate\Database\Query\Builder|WaypointNotes withoutTrashed()
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read int|null $audits_count
 * @mixin \Eloquent
 * @method static WaypointNotesFactory factory(...$parameters)
 */
class WaypointNotes extends Model implements Auditable
{
    use AuditableTrait;
    use SoftDeletes;
    use HasFactory;

    protected $connection = DbConnections::DEFAULT;

    public const MORPH_NAME = 'order-waypoint-note';

    public const TABLE = 'orders_waypoints_notes';
    protected $table = self::TABLE;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'user_id',
        'value'
    ];

    protected static function newFactory(): WaypointNotesFactory
    {
        return WaypointNotesFactory::new();
    }

    public function waypoint(): BelongsTo
    {
        return $this->belongsTo(Waypoint::class);
    }
}
