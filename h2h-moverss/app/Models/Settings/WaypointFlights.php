<?php

namespace App\Models\Settings;

use Database\Factories\Settings\WaypointFlightsFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Settings\WaypointFlights
 *
 * @property int $id
 * @property string|null $title
 * @property int $value
 * @property int $sort
 * @method static \Illuminate\Database\Eloquent\Builder|WaypointFlights newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WaypointFlights newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WaypointFlights query()
 * @method static \Illuminate\Database\Eloquent\Builder|WaypointFlights whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WaypointFlights whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WaypointFlights whereValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WaypointFlights whereSort($value)
 * @mixin \Eloquent
 * @method static WaypointFlightsFactory factory(...$parameters)
 */
class WaypointFlights extends Model
{
    use HasFactory;

    public const TABLE = 'settings_waypoints_flights';
    protected $table = self::TABLE;

    public $timestamps = false;

    protected static function newFactory(): WaypointFlightsFactory
    {
        return WaypointFlightsFactory::new();
    }
}
