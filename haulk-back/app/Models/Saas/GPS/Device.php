<?php

namespace App\Models\Saas\GPS;

use App\Casts\PhoneCast;
use App\Collections\Models\Saas\GPS\DeviceEloquentCollection;
use App\Enums\Saas\GPS\DeviceRequestStatus;
use App\Enums\Saas\GPS\DeviceStatus;
use App\Enums\Saas\GPS\DeviceStatusActivateRequest;
use App\Http\Controllers\Api\Helpers\DbConnections;
use App\ModelFilters\Saas\GPS\Devices\DeviceFilter;
use App\Models\GPS\History;
use App\Models\Saas\Company\Company;
use App\Models\Users\User;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Truck;
use App\Models\Vehicles\Vehicle;
use App\Traits\Filterable;
use App\ValueObjects\Phone;
use Database\Factories\Saas\GPS\DeviceFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * App\Models\Saas\GPS\Device
 *
 * @property int $id
 * @property int $flespi_device_id
 * @property DeviceStatus status
 * @property DeviceRequestStatus status_request
 * @property DeviceStatusActivateRequest status_activate_request
 * @property Phone phone
 * @property string $imei
 * @property string $name
 * @property string|null $company_device_name
 * @property int|null $company_id
 * @property Carbon|null $active_at
 * @property Carbon|null $inactive_at
 * @property Carbon|null $request_closed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property Carbon|null active_till_at
 * @property int|null $send_request_user_id
 * @property bool is_connected
 *
 * @see static::company()
 * @property Company|null company
 *
 * @see static::truck()
 * @property Truck|null truck
 *
 * @see static::trailer()
 * @property Trailer|null trailer
 *
 * @see static::sendRequestUser()
 * @property User|null sendRequestUser
 *
 * @see static::gpsHistories()
 * @property History|Collection gpsHistories
 *
 * @see static::histories()
 * @property DeviceHistory|Collection histories
 *
 * @see static::paymentItems()
 * @property DevicePayment|Collection paymentItems
 *
 * @see static::gpsSubscription()
 * @property DeviceSubscription|null gpsSubscription
 *
 * @see static::scopeWhereImei()
 * @method static Builder|static whereImei(string $imei)
 *
 * @method static DeviceFactory factory(...$parameters)
 *
 * @mixin Eloquent
 */
class Device extends Model
{
    use Filterable;
    use HasFactory;
    use SoftDeletes;

    public const DAYS_TO_FORCE_DELETE = 31; // через сколько дней девайс и его данные буду полностью удаленны

    public const TABLE_NAME = 'gps_devices';
    protected $table = self::TABLE_NAME;

    protected $connection = DbConnections::DEFAULT;

    protected $fillable = [
        'name',
        'imei',
        'company_id',
        'status',
        'phone',
        'request_closed_at',
        'status_request',
        'is_connected',
    ];

    protected $casts = [
        'status' => DeviceStatus::class,
        'status_activate_request' => DeviceStatusActivateRequest::class,
        'status_request' => DeviceRequestStatus::class,
        'phone' => PhoneCast::class,
        'active_at' => 'datetime',
        'inactive_at' => 'datetime',
        'deleted_at' => 'datetime',
        'request_closed_at' => 'datetime',
        'active_till_at' => 'datetime',
        'is_connected' => 'boolean',
    ];

    public function newCollection(array $models = []): DeviceEloquentCollection
    {
        return DeviceEloquentCollection::make($models);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function modelFilter(): string
    {
        return DeviceFilter::class;
    }

    public function truck(): HasOne
    {
        return $this->HasOne(Truck::class, 'gps_device_id');
    }

    public function paymentItems(): HasMany
    {
        return $this->HasMany(DevicePayment::class);
    }

    public function gpsHistories(): HasMany
    {
        return $this->HasMany(History::class);
    }

    public function histories(): HasMany
    {
        return $this->HasMany(DeviceHistory::class);
    }

    public function trailer(): HasOne
    {
        return $this->HasOne(Trailer::class, 'gps_device_id');
    }

    public function sendRequestUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'send_request_user_id');
    }

    public function vehicle(): ?Vehicle
    {
        return $this->truck ?: $this->trailer;
    }

    public function gpsSubscription(): HasOneThrough
    {
        return $this->hasOneThrough(
            DeviceSubscription::class,
            Company::class,
            'id',
            'company_id',
            'company_id',
            'id'
        );
    }

    public function scopeImei(Builder $query, string $imei)
    {
        $query->where('imei', $imei);
    }

    public function isConnected(): bool
    {
        return $this->is_connected;
    }
}
