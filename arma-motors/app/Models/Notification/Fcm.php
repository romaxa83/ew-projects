<?php

namespace App\Models\Notification;

use App\Models\BaseModel;
use App\Models\User\User;
use App\Services\Telegram\TelegramDev;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * App\Models\Notification\Fcm
 *
 * @property int $id
 * @property string $user_id
 * @property string $entity_type
 * @property int $entity_id
 * @property string $status
 * @property string $action
 * @property string $type
 * @property string $response_data
 * @property string $send_data
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 */
class Fcm extends BaseModel
{
    use HasFactory;

    public const STATUS_CREATED = 'created';
    public const STATUS_SEND = 'send';
    public const STATUS_HAS_ERROR = 'has_error';

    public const TYPE_COMPLETE = 'complete';
    public const TYPE_PAYMENT = 'payment';
    public const TYPE_SYSTEM = 'system';
    public const TYPE_NEW = 'new';
    public const TYPE_MESSAGE = 'message';
    public const TYPE_ALERT = 'alert';
    public const TYPE_PERCENT = 'percent';
    public const TYPE_CUPON = 'cupon';
    public const TYPE_EMAIL = 'email'; // смена email
    public const TYPE_PHONE = 'phone'; // смена email

    public const TABLE_NAME = 'fcm_notifications';

    protected $table = self::TABLE_NAME;

    protected $casts = [
        'send_data' => 'array',
        'response_data' => 'array'
    ];

    public static function boot()
    {
        \Log::info('Before boot');
        parent::boot();
        \Log::info('After boot');
    }

    public function entity(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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

    public function scopeFcmStatus(EloquentBuilder $query, $status): EloquentBuilder
    {
        return $query->where('status', $status);
    }

    public function scopeFcmType(EloquentBuilder $query, $type): EloquentBuilder
    {
        return $query->where('type', $type);
    }

    public function scopeUserSearch(EloquentBuilder $query, $userId): EloquentBuilder
    {
        return $query->where('user_id', $userId);
    }
}

