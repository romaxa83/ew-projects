<?php

namespace App\Models\GPS;

use App\Http\Controllers\Api\Helpers\DbConnections;
use Database\Factories\GPS\MessageFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 *
 * @property int id
 * @property Carbon received_at
 * @property string imei
 * @property string|null longitude
 * @property string|null latitude
 * @property float|null speed
 * @property float|null heading
 * @property float|null vehicle_mileage
 * @property bool|null driving
 * @property bool|null idling
 * @property bool|null engine_off
 * @property int|null device_battery_level
 * @property bool|null device_battery_charging_status
 * @property array|null data
 * @property bool|null movement_status
 * @property int|null position_satellites
 * @property bool|null position_valid
 * @property Carbon|null server_time_at
 * @property int|null gsm_signal_level
 * @property float|null gps_fuel_rate
 * @property float|null gps_fuel_used
 * @property float|null external_powersource_voltage
 *
 * @method static MessageFactory factory(...$parameters)
 *
 * @mixin Eloquent
 */
class Message extends Model
{
    use HasFactory;

    public const TABLE_NAME = 'messages';

    protected $connection = DbConnections::GPS;

    protected $table = self::TABLE_NAME;

    public $timestamps = false;

    protected $fillable = [
        'received_at',
        'imei',
        'latitude',
        'longitude',
        'speed',
        'heading',
        'vehicle_mileage',
        'driving',
        'idling',
        'engine_off',
        'device_battery_level',
        'device_battery_charging_status',
        'data',
        'movement_status',
        'position_satellites',
        'position_valid',
        'server_time_at',
        'gsm_signal_level',
        'gps_fuel_rate',
        'gps_fuel_used',
        'external_powersource_voltage',
    ];

    protected $casts = [
        'server_time_at' => 'datetime',
        'received_at' => 'datetime',
        'data' => 'array',
        'driving' => 'boolean',
        'idling' => 'boolean',
        'engine_off' => 'boolean',
        'device_battery_charging_status' => 'boolean',
        'movement_status' => 'boolean',
        'position_valid' => 'boolean',
    ];
}
