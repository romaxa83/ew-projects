<?php

namespace App\Models\Vehicles;

use App\Http\Controllers\Api\Helpers\DbConnections;
use App\ModelFilters\Vehicles\TruckFilter;
use App\Models\BodyShop\Orders\Order;
use App\Models\GPS\History;
use App\Models\Saas\GPS\Device;
use App\Models\Users\User;
use App\Models\Vehicles\Comments\TruckComment;
use App\Scopes\CompanyScope;
use Carbon\Carbon;
use Database\Factories\Vehicles\TruckFactory;
use FontLib\TrueType\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property string $unit_number
 * @property integer $type
 * @property int $carrier_id
 * @property int $driver_id
 * @property int $gps_device_id
 * @property int $last_gps_history_id
 * @property Carbon|null $last_driving_at
 * @property float|null $gvwr
 *
 * @see static::gpsDevice()
 * @property Device|null gpsDevice
 *
 * @see static::gpsDeviceWithTrashed()
 * @property Device|null gpsDeviceWithTrashed
 *
 * @see static::driver()
 * @property User|null driver
 *
 * @see static::lastGPSHistory()
 * @property History|null lastGPSHistory
 *
 * @see static::gpsHistories()
 * @property History|Collection gpsHistories
 *
 * @see static::histories()
 * @property \App\Models\History\History|Collection histories
 *
 * @method static TruckFactory factory(...$parameters)
 */
class Truck extends Vehicle
{
   public const TABLE_NAME = 'trucks';

   protected $table = self::TABLE_NAME;

    protected $connection = DbConnections::DEFAULT;

    protected $fillable = [
        'unit_number',
        'vin',
        'make',
        'model',
        'year',
        'type',
        'license_plate',
        'temporary_plate',
        'notes',
        'owner_id',
        'driver_id',
        'carrier_id',
        'broker_id',
        'customer_id',
        'color',
        'registration_number',
        'registration_date',
        'registration_expiration_date',
        'inspection_number',
        'inspection_date',
        'inspection_expiration_date',
        'gps_device_id',
        'last_gps_history_id',
        'gvwr',
        'registration_date_as_str',
        'registration_expiration_date_as_str',
        'inspection_date_as_str',
        'inspection_expiration_date_as_str',
    ];

    protected $casts = [
        'last_driving_at' => 'datetime',
        'registration_expiration_date' => 'date',
        'registration_date' => 'date',
        'inspection_date' => 'date',
        'inspection_expiration_date' => 'date',
        'gvwr' => 'float',
    ];

   public function modelFilter()
   {
        return $this->provideFilter(TruckFilter::class);
   }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'truck_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TruckComment::class);
    }

    public function driversHistory(): HasMany
    {
        return $this->hasMany(TruckDriverHistory::class, 'truck_id');
    }

    public function ownersHistory(): HasMany
    {
        return $this->hasMany(TruckOwnerHistory::class, 'truck_id');
    }

    public function gpsDevice(): HasOne
    {
        return $this->hasOne(Device::class, 'id', 'gps_device_id');
    }

    public function gpsDeviceWithTrashed(): HasOne
    {
        return $this->gpsDevice()->withTrashed();
    }

    public function lastGPSHistory(): HasOne
    {
        return $this->hasOne(History::class, 'id',  'last_gps_history_id');
    }

    public function gpsHistories(): HasMany
    {
        return $this->hasMany(History::class);
    }

    public function driver(): ?BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id')
            ->withoutGlobalScope(new CompanyScope());
    }
}
