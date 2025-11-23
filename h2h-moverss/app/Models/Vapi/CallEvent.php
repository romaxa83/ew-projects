<?php

namespace App\Models\Vapi;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $call_id
 * @property string $type_event
 * @property string $type_call
 * @property string $reason_ended
 * @property string $caller_number
 * @property int $duration // second
 * @property string|null $recording_url
 * @property Carbon|null $call_start_at
 * @property Carbon|null $call_end_at
 * @property array|null $misc
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @see self::clientRequest()
 * @property-read ClientRequest|null clientRequest
 *
 * @mixin \Eloquent
 */
class CallEvent extends Model
{
    public const TABLE = 'vapi_call_events';
    protected $table = self::TABLE;

    public const MORPH_NAME = 'vapi-call-event';
    public const TYPE_EVENT_END_OF_CALL = 'end-of-call-report';
    public const TYPE_CALL_INBOUND = 'inboundPhoneCall';
    public const REASON_ENDED_AS_TRANSFER = 'assistant-forwarded-call';

    protected $dates = [
        'call_start_at',
        'call_end_at',
    ];

    protected $fillable = [];

    protected $casts = [
        'misc' => 'array',
    ];

    public function isInboundCall(): bool
    {
        return $this->type_call === self::TYPE_CALL_INBOUND;
    }

    public function isTransfer(): bool
    {
        return $this->reason_ended === self::REASON_ENDED_AS_TRANSFER;
    }

    public function clientRequest(): HasOne
    {
        return $this->hasOne(ClientRequest::class, 'call_rec_id');
    }
}

