<?php

namespace App\Models\Saas\GPS;

use App\Enums\Saas\GPS\Request\DeviceRequestSource;
use App\Enums\Saas\GPS\Request\DeviceRequestStatus;
use App\Http\Controllers\Api\Helpers\DbConnections;
use App\ModelFilters\Saas\GPS\Devices\DeviceRequestFilter;
use App\Models\Saas\Company\Company;
use App\Models\Users\User;
use App\Traits\Filterable;
use Database\Factories\Saas\GPS\DeviceRequestFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\Saas\GPS\Device
 *
 * @property int $id
 * @property int company_id
 * @property int user_id
 * @property DeviceRequestStatus status
 * @property DeviceRequestSource source
 * @property int qty
 * @property string|null comment
 * @property Carbon|null $closed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @see static::company()
 * @property Company company
 *
 * @see static::user()
 * @property User user
 *
 * @method static DeviceRequestFactory factory(...$parameters)
 *
 * @mixin Eloquent
 */
class DeviceRequest extends Model
{
    use Filterable;
    use HasFactory;

    public const TABLE_NAME = 'gps_device_requests';
    protected $table = self::TABLE_NAME;

    protected $connection = DbConnections::DEFAULT;

    protected $fillable = [
        'status',
        'comment',
        'closed_at'
    ];

    protected $casts = [
        'status' => DeviceRequestStatus::class,
        'source' => DeviceRequestSource::class,
        'closed_at' => 'datetime',
    ];

    public function modelFilter(): string
    {
        return DeviceRequestFilter::class;
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

