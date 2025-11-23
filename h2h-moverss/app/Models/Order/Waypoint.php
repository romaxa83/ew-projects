<?php

namespace App\Models\Order;

use App\Helpers\DbConnections;
use Database\Factories\Orders\WaypointFactory;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\{Factories\HasFactory, SoftDeletes, Model};
use App\Models\{ParkingType, BuildingType, Settings\WaypointFlights};

/**
 * App\Models\Order\Waypoint
 *
 * @property int $id
 * @property int $order_id
 * @property string $type
 * @property string $state
 * @property string $zip
 * @property string|null $city
 * @property string|null $address
 * @property string|null $ap
 * @property int $parking_type_id
 * @property int $has_elevator
 * @property int $building_type_id
 * @property int $flights_id
 * @property int $sort
 * @property float|null $lat
 * @property float|null $lng
 * @property array|null $miscs
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read BuildingType|null $buildingType
 * @property-read \App\Models\Settings\WaypointFlights|null $flights
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Order\WaypointNotes[] $notes
 * @property-read int|null $notes_count
 * @property-read ParkingType|null $parkingType
 * @method static \Illuminate\Database\Eloquent\Builder|Waypoint newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Waypoint newQuery()
 * @method static \Illuminate\Database\Query\Builder|Waypoint onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Waypoint query()
 * @method static \Illuminate\Database\Eloquent\Builder|Waypoint whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Waypoint whereAp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Waypoint whereBuildingTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Waypoint whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Waypoint whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Waypoint whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Waypoint whereFlightsId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Waypoint whereHasElevator($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Waypoint whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Waypoint whereLat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Waypoint whereLng($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Waypoint whereMiscs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Waypoint whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Waypoint whereParkingTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Waypoint whereSort($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Waypoint whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Waypoint whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Waypoint whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Waypoint whereZip($value)
 * @method static \Illuminate\Database\Query\Builder|Waypoint withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Waypoint withoutTrashed()
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read int|null $audits_count
 * @mixin \Eloquent
 * @method static WaypointFactory factory(...$parameters)
 */
class Waypoint extends Model implements Auditable
{
    use AuditableTrait;
    use SoftDeletes;
    use HasFactory;

    public const MORPH_NAME = 'order-waypoint';

    public const TABLE = 'orders_waypoints';
    protected $table = self::TABLE;

    protected $connection = DbConnections::DEFAULT;

    protected $dates = ['deleted_at'];
    protected $fillable = [
        'type',
        'state',
        'zip',
        'city',
        'address',
        'ap',
        'building_type_id',
        'flights_id',
        'parking_type_id',
        'has_elevator',
        'sort',
        'lat',
        'lng',
        'miscs',
    ];
    protected $casts = [
        'lat' => 'float',
        'lng' => 'float',
        'miscs' => 'array',
    ];

    protected static function newFactory(): WaypointFactory
    {
        return WaypointFactory::new();
    }

    public function parkingType(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ParkingType::class, 'id', 'parking_type_id');
    }

    public function buildingType(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(BuildingType::class, 'id', 'building_type_id');
    }

    public function notes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(WaypointNotes::class);
    }

    public function flights(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(WaypointFlights::class, 'id', 'flights_id');
    }

    public function getRouteName()
    {
        $names = [];
        if (!empty($this->address)) {
            $names[] = $this->address;
        }

        if (!empty($this->city) && !str_contains($this->address, $this->city)) {
            $names[] = $this->city;
        }

        $string = $names ? implode(', ', $names) . ', ' : '';

        if (!empty($this->state) && !str_contains($string, $this->state)) {
            $string .= ' ' . $this->state;
        }
        if (!empty($this->zip) && !str_contains($string, $this->zip)) {
            $string .= ' ' . $this->zip;
        }

        return $string;
    }
}
