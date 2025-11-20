<?php

namespace App\Models\Notification;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FcmNotification extends Model
{
    use HasFactory;

    public const STATUS_CREATED = 'created';
    public const STATUS_SEND = 'send';
    public const STATUS_HAS_ERROR = 'has_error';

    public const TABLE_NAME = 'fcm_notifications';
    protected $table = self::TABLE_NAME;

    protected $casts = [
        'send_data' => 'array',
        'response_data' => 'array'
    ];

    public function entity()
    {
        return $this->morphTo();
    }

    public function hasError(): bool
    {
        return $this->status === self::STATUS_HAS_ERROR;
    }

    public function isSend(): bool
    {
        return $this->status === self::STATUS_SEND;
    }

    public function setError($message): self
    {
        $this->status = self::STATUS_HAS_ERROR;
        $this->response_data = $message;
        $this->save();

        return $this;
    }

    public function setSendStatus($message): self
    {
        $this->status = self::STATUS_SEND;
        $this->response_data = $message;
        $this->save();

        return $this;
    }
}
