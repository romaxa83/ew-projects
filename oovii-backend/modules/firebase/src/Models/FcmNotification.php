<?php

namespace WezomCms\Firebase\Models;

use Illuminate\Database\Eloquent\Model;
use WezomCms\Core\Traits\Model\Filterable;
use WezomCms\Users\Models\User;

/**
 * @property int $id
 * @property int|null $user_id
 * @property string|null $entity_type
 * @property int|null $entity_id
 * @property string $type
 * @property string $status
 * @property string|null $send_data
 * @property string|null $response_data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class FcmNotification extends Model
{
    use Filterable;

    const STATUS_CREATED = 'created';
    const STATUS_SEND    = 'send';
    const STATUS_ERROR   = 'error';

    public const TABLE = 'fcm_notifications';
    protected $table = self::TABLE;

    protected $casts = [
        'send_data' => 'array',
        'response_data' => 'array'
    ];

    // relation
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function entity()
    {
        return $this->morphTo();
    }

    public function setError($msg): self
    {
        $this->status = self::STATUS_ERROR;
        $this->response_data = $msg;
        $this->save();

        return $this;
    }

    public function setSendStatus($msg): self
    {
        $this->status = self::STATUS_SEND;
        $this->response_data = $msg;
        $this->save();

        return $this;
    }
}



