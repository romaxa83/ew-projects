<?php

namespace App\Models\GPS;

use App\Http\Controllers\Api\Helpers\DbConnections;
use App\ModelFilters\Saas\GPS\History\HistoryFilter;
use App\Models\Saas\Company\Company;
use App\Models\Saas\GPS\Device;
use App\Models\Users\User;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Truck;
use App\Traits\Filterable;
use Database\Factories\GPS\HistoryFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 *
 * @property int $id
 * @property Carbon $created_at
 * @property Carbon|null $updated_at
 * @property Carbon $received_at
 * @property int|null $truck_id
 * @property int|null $device_id
 * @property int|null $trailer_id
 * @property int|null $driver_id
 * @property int|null $old_driver_id    // ссылка на старого водителя, при смене водителей
 * @property int|null $company_id
 * @property string|null $longitude
 * @property string|null $latitude
 * @property float|null $vehicle_mileage
 * @property float|null $speed
 * @property float|null $heading
 * @property int|null $device_battery_level
 * @property bool|null $device_battery_charging_status
 * @property string $event_type
 * @property int msg_count_for_duration
 * @property string|null hash
 * @property int $event_duration
 * @property array|null data
 * @property bool is_speeding
 * @property bool sleep_mode
 * @property Carbon last_received_at
 *
 * @property-read HasMany|Alert[] $alerts
 * @property-read BelongsTo|Truck $truck
 * @property-read BelongsTo|Trailer $trailer
 * @property-read BelongsTo|Device $device
 * @property-read BelongsTo|Device $deviceWithTrashed
 *
 * @see static::driver()
 * @property User|null driver
 *
 * @see static::oldDriver()
 * @property User|null oldDriver
 *
 * @method static HistoryFactory factory(...$parameters)
 *
 * @mixin Eloquent
 */
class History extends Model
{
    use Filterable;
    use HasFactory;

    public const TABLE_NAME = 'history';

    public const EVENT_DRIVING = 'driving';
    public const EVENT_IDLE = 'idle';
    public const EVENT_LONG_IDLE = 'long_idle';
    public const EVENT_ENGINE_OFF = 'engine_off';
    public const EVENT_CHANGE_DRIVER = 'change_driver';

    public const EVENT_TRAILER_STOPPED = 'stooped';

    protected $connection = DbConnections::GPS;

    protected $table = self::TABLE_NAME;

    protected $fillable = [
        'received_at',
        'truck_id',
        'trailer_id',
        'device_id',
        'latitude',
        'longitude',
        'heading',
        'vehicle_mileage',
        'driver_id',
        'speed',
        'device_battery_level',
        'device_battery_charging_status',
        'event_type',
        'event_duration',
        'company_id',
        'data',
        'is_speeding',
        'msg_count_for_duration',
        'hash',
        'last_received_at',
        'sleep_mode',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'received_at' => 'datetime',
        'last_received_at' => 'datetime',
        'data' => 'array',
        'latitude' => 'double',
        'longitude' => 'double',
        'is_speeding' => 'bool',
        'sleep_mode' => 'bool',
    ];

    public function modelFilter(): string
    {
        return HistoryFilter::class;
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class, 'history_id');
    }

    public function truck(): BelongsTo
    {
        return $this->belongsTo(Truck::class);
    }

    public function trailer(): BelongsTo
    {
        return $this->belongsTo(Trailer::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function deviceWithTrashed(): BelongsTo
    {
        return $this->device()->withTrashed();
    }

    public function driver(): ?BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function oldDriver(): ?BelongsTo
    {
        return $this->belongsTo(User::class, 'old_driver_id');
    }

    public function hasConnectionLostAlerts(): bool
    {
        return $this->alerts
            ->pluck('alert_type')
            ->contains(Alert::ALERT_DEVICE_CONNECTION_LOST)
            ;
    }

    public function hasConnectionRestoreAlerts(): bool
    {
        return $this->alerts
            ->pluck('alert_type')
            ->contains(Alert::ALERT_DEVICE_CONNECTION_RESTORED)
            ;
    }
}
