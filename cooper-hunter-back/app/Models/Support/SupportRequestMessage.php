<?php

namespace App\Models\Support;

use App\Events\SupportRequests\SupportRequestMessageSavedEvent;
use App\Models\BaseModel;
use App\Traits\HasFactory;
use Database\Factories\Support\SupportRequestMessageFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * @property int id
 * @property int support_request_id
 * @property string message
 * @property string sender_type
 * @property int sender_id
 * @property bool is_read
 * @property Carbon|null created_at
 * @property Carbon|null updated_at
 *
 * @method static SupportRequestMessageFactory factory(...$parameters)
 */
class SupportRequestMessage extends BaseModel
{
    use HasFactory;

    public const TABLE = 'support_request_messages';

    protected $fillable = [
        'support_request_id',
        'sender_id',
        'sender_type',
        'is_read',
        'message',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'support_request_id' => 'int',
        'sender_id' => 'int',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $dispatchesEvents = [
        'saved' => SupportRequestMessageSavedEvent::class,
    ];

    public function supportRequest(): BelongsTo
    {
        return $this->belongsTo(SupportRequest::class);
    }

    public function sender(): MorphTo
    {
        return $this->morphTo();
    }
}
