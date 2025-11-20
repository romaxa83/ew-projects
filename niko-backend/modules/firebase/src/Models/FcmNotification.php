<?php

namespace WezomCms\Firebase\Models;

use Illuminate\Database\Eloquent\Model;
use WezomCms\Firebase\Types\FcmNotificationStatus;
use WezomCms\Firebase\Types\FcmNotificationType;
use WezomCms\ServicesOrders\Models\ServicesOrder;
use WezomCms\Users\Models\User;

/**
 * @property int $id
 * @property int $user_id
 * @property int|null $service_order_id
 * @property int $type
 * @property int $status
 * @property string $data
 * @property string|null $error_data
 * @property string|null $success_data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class FcmNotification extends Model
{
    const STATUS_SUCCESS = 1;

    protected $table = 'fcm_notifications';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'service_order_id',
        'type',
        'status',
        'data',
        'error_data',
        'success_data'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'data' => 'array',
        'error_data' => 'array',
        'success_data' => 'array'
    ];

    public static function create($userId, $type, $data, $orderId = null): self
    {
        $noty = new self();
        $noty->user_id = $userId;
        $noty->type = $type;
        $noty->data = $data;
        $noty->service_order_id = $orderId;
        $noty->save();

        return $noty;
    }

    public function setError($error): self
    {
        $this->status = FcmNotificationStatus::HAS_ERROR;
        $this->error_data = $error;
        $this->save();

        return $this;
    }

    public function setSuccessData($data): self
    {
        $this->success_data = $data;
        $this->save();

        return $this;
    }

    // relation
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(ServicesOrder::class,  'service_order_id', 'id');
    }
}


