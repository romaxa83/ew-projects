<?php

namespace App\Models\Saas\GPS;

use App\Enums\BaseEnum;
use App\Enums\Saas\GPS\DeviceHistoryContext;
use App\Enums\Saas\GPS\DeviceRequestStatus;
use App\Http\Controllers\Api\Helpers\DbConnections;
use App\ModelFilters\Saas\GPS\Devices\DeviceHistoryFilter;
use App\Models\Vehicles\Vehicle;
use App\Traits\Filterable;
use App\ValueObjects\Phone;
use Database\Factories\Saas\GPS\DeviceHistoryFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int device_id
 * @property DeviceHistoryContext context
 * @property array changed_data
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @see static::device()
 * @property Device device
 *
 * @method static DeviceHistoryFactory factory(...$parameters)
 *
 * @mixin Eloquent
 */
class DeviceHistory extends Model
{
    use Filterable;
    use HasFactory;

    public const TABLE_NAME = 'gps_device_histories';
    protected $table = self::TABLE_NAME;

    protected $connection = DbConnections::DEFAULT;

    protected $fillable = [];

    protected $casts = [
        'context' => DeviceHistoryContext::class,
        'changed_data' => 'array',
    ];

    public function modelFilter(): string
    {
        return DeviceHistoryFilter::class;
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public static function attachOrDetachVehicle(Vehicle $vehicle, ?int $deviceId = null)
    {
        if($vehicle->gps_device_id === null && $deviceId !== null){
            DeviceHistory::create($deviceId, DeviceHistoryContext::ATTACH_TO_VEHICLE(), [
                'old' => [],
                'new' => ['vehicle_type' => get_class($vehicle), 'vehicle_id' => $vehicle->id]
            ]);
        }
        if($vehicle->gps_device_id !== null && $deviceId === null){
            DeviceHistory::create($vehicle->gps_device_id, DeviceHistoryContext::DETACH_TO_VEHICLE(), [
                'old' => ['vehicle_type' => get_class($vehicle), 'vehicle_id' => $vehicle->id],
                'new' => []
            ]);
        }
    }

    public static function create(
        $device,
        DeviceHistoryContext $context,
        array $data = []
    )
    {
        $model = new DeviceHistory();
        $model->device_id = $device instanceof Device ? $device->id : $device;
        $model->context = $context;
        $model->changed_data = !empty($data) ? $data : self::getChanged($device);

        $model->save();
    }

    public static function createRemoveClosedStatus(Device $device)
    {
        $model = new DeviceHistory();
        $model->device_id = $device->id;
        $model->context = DeviceHistoryContext::REMOVE_REQUEST_CLOSED();
        $model->changed_data = [
            'old' => [
                'request_closed_at' => $device->request_closed_at,
                'status_request' => $device->status_request
            ],
            'new' => [
                'request_closed_at' => null,
                'status_request' => DeviceRequestStatus::NONE()
            ],
        ];

        $model->save();
    }

    public static function createPayment(
        Device $device,
        DevicePayment $paymentItem
    )
    {
        $model = new DeviceHistory();
        $model->device_id = $device->id;
        $model->context = DeviceHistoryContext::PAYMENT_REC();
        $model->changed_data = [
            'old' => [],
            'new' => [
                'company_id' => $paymentItem->company_id,
                'amount' => $paymentItem->amount,
                'date' => $paymentItem->date
            ],
        ];

        $model->save();
    }

    public static function createPaymentDelete(
        DevicePayment $paymentItem,
        ?Carbon $startAt = null,
        ?Carbon $endAt = null
    )
    {
        $model = new DeviceHistory();
        $model->device_id = $paymentItem->device_id;
        $model->context = DeviceHistoryContext::PAYMENT_REC_DELETE();
        $model->changed_data = [
            'old' => [
                'company_id' => $paymentItem->company_id,
                'amount' => $paymentItem->amount,
                'date' => $paymentItem->date,
                'billing_start_at' => $startAt ?? $startAt->toDateTimeString(),
                'billing_end_at' => $endAt ?? $endAt->toDateTimeString()
            ],
            'new' => [],
        ];

        $model->save();
    }

    private static function getChanged(Device $model): array
    {
        $data = [];
        if(!empty($model->getDirty())){
            $data['new'] = $model->getDirty();
            $data['old'] = [];
            foreach ($model->getDirty() as $field => $value){
                if(empty($model->getOriginal())) continue;

                $prettyValue = $model->getOriginal()[$field];

                if($model->getOriginal()[$field] instanceof BaseEnum){
                    $prettyValue = $model->getOriginal()[$field]->value;
                }

                $data['old'][$field] = $prettyValue;
            }
        } elseif (
            empty($model->getDirty())
            && !empty($model->getOriginal())
        ) {
            // создание
            $data['old'] = [];
            foreach ($model->getOriginal() as $field => $value){

                $prettyValue = $value;
                if($value instanceof BaseEnum){
                    $prettyValue = $value->value;
                }
                if($value instanceof Phone){
                    $prettyValue = $value->getValue();
                }
                if($value instanceof Carbon){
                    $prettyValue = $value->toDateTimeString();
                }

                $data['new'][$field] = $prettyValue;
            }
        }

        return $data;
    }
}
