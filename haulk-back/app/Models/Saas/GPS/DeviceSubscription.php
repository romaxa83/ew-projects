<?php

namespace App\Models\Saas\GPS;

use App\Collections\Models\Saas\GPS\DeviceEloquentCollection;
use App\Enums\Saas\GPS\DeviceStatus;
use App\Enums\Saas\GPS\DeviceSubscriptionStatus;
use App\Http\Controllers\Api\Helpers\DbConnections;
use App\Models\Saas\Company\Company;
use Database\Factories\Saas\GPS\DeviceSubscriptionFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Carbon;

/**
 * @property int id
 * @property DeviceSubscriptionStatus status
 * @property int company_id
 * @property Carbon|null activate_at
 * @property Carbon|null activate_till_at
 * @property Carbon|null canceled_at
 * @property Carbon|null created_at
 * @property Carbon|null updated_at
 * @property Carbon|null access_till_at
 * @property boolean send_warning_notify
 * @property float current_rate         // текущая цена за девайс
 * @property float|null next_rate       // цена за девайс со следуещего билинг периода
 *
 * @see static::company()
 * @property Company|null company
 *
 * @see static::devices()
 * @property Device|DeviceEloquentCollection devices
 *
 * @method static DeviceSubscriptionFactory factory(...$parameters)
 *
 * @mixin Eloquent
 */
class DeviceSubscription extends Model
{
    use HasFactory;

    public const TABLE_NAME = 'gps_device_subscriptions';
    protected $table = self::TABLE_NAME;

    protected $connection = DbConnections::DEFAULT;

    protected $fillable = [
        'canceled_at',
        'activate_till_at',
        'status',
        'access_till_at',
        'send_warning_notify',
    ];

    protected $casts = [
        'status' => DeviceSubscriptionStatus::class,
        'activate_at' => 'datetime',
        'activate_till_at' => 'datetime',
        'canceled_at' => 'datetime',
        'access_till_at' => 'datetime',
        'send_warning_notify' => 'boolean',
        'current_rate' => 'float',
        'next_rate' => 'float',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function devices(): HasManyThrough
    {
        return $this->hasManyThrough(
            Device::class,
            Company::class,
            'id',
            'company_id',
            'company_id',
            'id'
        );
    }

    public function devicesActive(): HasManyThrough
    {
        return $this->hasManyThrough(
            Device::class,
            Company::class,
            'id',
            'company_id',
            'company_id',
            'id'
        )->where(Device::TABLE_NAME.'.status', DeviceStatus::ACTIVE);
    }

    public function devicesCount(): int
    {
        return $this->devices()->count();
    }

    public function devicesCountByStatus(DeviceStatus $status): int
    {
        return $this->devices()
            ->withTrashed()
            ->where(Device::TABLE_NAME.'.status', $status)
            ->count();
    }
}

