<?php

namespace App\Models\GPS;

use App\Http\Controllers\Api\Helpers\DbConnections;
use App\ModelFilters\GPS\AlertFilter;
use App\Models\Saas\Company\Company;
use App\Models\Users\User;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Truck;
use App\Traits\Filterable;
use Database\Factories\GPS\AlertFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 *
 * @property int $id
 * @property Carbon $received_at
 * @property int|null $truck_id
 * @property int|null $trailer_id
 * @property int|null $driver_id
 * @property string|null $longitude
 * @property string|null $latitude
 * @property float|null $speed
 * @property string $alert_type
 * @property string|null $alert_subtype;
 * @property int|null $history_id
 * @property int|null $company_id
 * @property int|null $device_id
 * @property string|null min_lost_connection
 *
 * @see static::truck()
 * @property  Truck|null truck
 *
 * @see static::trailer()
 * @property  Trailer|null trailer
 *
 * @see static::driver()
 * @property  User|null driver
 *
 * @see static::company()
 * @property Company company
 *
 * @method static AlertFactory factory(...$parameters)
 *
 * @mixin Eloquent
 */
class Alert extends Model
{
    use Filterable;
    use HasFactory;

    public const TABLE_NAME = 'alerts';

    public const ALERT_TYPE_SPEEDING = 'speeding';
    public const ALERT_TYPE_DEVICE_CONNECTION = 'device_connection';
    public const ALERT_TYPE_DEVICE_BATTERY = 'device_battery';
    public const ALERT_DEVICE_CONNECTION_LOST = 'device_connection_lost';
    public const ALERT_DEVICE_CONNECTION_RESTORED = 'device_connection_restored';

    public const ALERT_SUBTYPE_BATTERY_STOP_CHARGING = 'stop_charging';
    public const ALERT_SUBTYPE_BATTERY_LOW = 'low';

    public const DETAILS_MESSAGE_SPEEDING = 'gps.details_message_speeding';
    public const DETAILS_MESSAGE_DEVICE_CONNECTION = 'gps.details_message_device_connection';
    public const DETAILS_MESSAGE_DEVICE_CONNECTION_LOST = 'gps.details_message_device_connection_lost';
    public const DETAILS_MESSAGE_DEVICE_CONNECTION_RESTORED = 'gps.details_message_device_connection_restored';
    public const DETAILS_MESSAGE_BATTERY_LOW = 'gps.details_message_battery_low';
    public const DETAILS_MESSAGE_BATTERY_STOP_CHARGING = 'gps.details_message_battery_stop_charging';

    public const ALERT_TYPES = [
        self::ALERT_TYPE_SPEEDING,
        self::ALERT_TYPE_DEVICE_BATTERY,
        self::ALERT_DEVICE_CONNECTION_LOST,
        self::ALERT_DEVICE_CONNECTION_RESTORED,
    ];

    protected $connection = DbConnections::GPS;

    protected $table = self::TABLE_NAME;

    public $timestamps = false;

    protected $fillable = [
        'received_at',
        'truck_id',
        'trailer_id',
        'latitude',
        'longitude',
        'driver_id',
        'speed',
        'alert_type',
        'alert_subtype',
        'history_id',
        'company_id',
        'min_lost_connection',
        'device_id',
    ];

    protected $casts = [
        'received_at' => 'datetime',
        'latitude' => 'double',
        'longitude' => 'double',
    ];

    public function modelFilter(): string
    {
        return $this->provideFilter(AlertFilter::class);
    }

    public function truck(): ?BelongsTo
    {
        return $this->belongsTo(Truck::class, 'truck_id');
    }

    public function trailer(): ?BelongsTo
    {
        return $this->belongsTo(Trailer::class, 'trailer_id');
    }

    public function company(): ?BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function driver(): ?BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function getDetailsMessage(): string
    {
        if ($this->isSpeeding()) {
            return trans(self::DETAILS_MESSAGE_SPEEDING, ['speed' => round($this->speed, 2)]);
        }

        if ($this->isDeviceConnection()) {
            return trans(self::DETAILS_MESSAGE_DEVICE_CONNECTION, ['min' => $this->min_lost_connection]);
        }

        if ($this->isDeviceConnectionLost()) {
            return trans(self::DETAILS_MESSAGE_DEVICE_CONNECTION_LOST, ['min' => $this->min_lost_connection]);
        }

        if ($this->isDeviceConnectionRestored()) {
            return trans(self::DETAILS_MESSAGE_DEVICE_CONNECTION_RESTORED);
        }

        if ($this->isBatteryLow()) {
            return trans(self::DETAILS_MESSAGE_BATTERY_LOW);
        }

        return trans(self::DETAILS_MESSAGE_BATTERY_STOP_CHARGING);
    }

    public function isSpeeding(): bool
    {
        return $this->alert_type === self::ALERT_TYPE_SPEEDING;
    }

    private function isDeviceConnection(): bool
    {
        return $this->alert_type === self::ALERT_TYPE_DEVICE_CONNECTION;
    }

    private function isDeviceConnectionLost(): bool
    {
        return $this->alert_type === self::ALERT_DEVICE_CONNECTION_LOST;
    }

    private function isDeviceConnectionRestored(): bool
    {
        return $this->alert_type === self::ALERT_DEVICE_CONNECTION_RESTORED;
    }

    private function isDeviceBattery(): bool
    {
        return $this->alert_type === self::ALERT_TYPE_DEVICE_BATTERY;
    }

    private function isBatteryLow(): bool
    {
        return $this->isDeviceBattery() && $this->alert_subtype === self::ALERT_SUBTYPE_BATTERY_LOW;
    }

    private function isBatteryStopCharging(): bool
    {
        return $this->isDeviceBattery() && $this->alert_subtype === self::ALERT_SUBTYPE_BATTERY_STOP_CHARGING;
    }
}
