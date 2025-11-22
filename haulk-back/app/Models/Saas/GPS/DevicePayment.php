<?php

namespace App\Models\Saas\GPS;

use App\Http\Controllers\Api\Helpers\DbConnections;
use App\Models\Saas\Company\Company;
use Database\Factories\Saas\GPS\DevicePaymentFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int company_id
 * @property int device_id
 * @property float amount
 * @property boolean deactivate
 * @property Carbon date
 *
 * @see static::device()
 * @property Device device
 *
 * @see static::company()
 * @property Company company
 *
 * @method static DevicePaymentFactory factory(...$parameters)
 *
 * @mixin Eloquent
 */
class DevicePayment extends Model
{
    use HasFactory;

    public const TABLE_NAME = 'gps_device_payments';
    protected $table = self::TABLE_NAME;

    public $timestamps = false;

    protected $connection = DbConnections::DEFAULT;

    protected $fillable = [];

    protected $casts = [
        'date' => 'date',
        'amount' => 'float',
        'deactivate' => 'boolean',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}

