<?php

namespace App\Models\Ringostat;

use App\Helpers\DbConnections;
use App\Models\Client\Phone;
use App\Models\Communications\CommunicationRecord;
use Database\Factories\Ringostat\EventAfterCallFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * App\Models\Ringostat\EventAfterCall
 *
 * @property int $id
 * @property int|null $project_id
 * @property string|null $call_id
 * @property string|null $type
 * @property string|null $scheme_name
 * @property string|null $status
 * @property string|null $destination
 * @property string|null $number_e164
 * @property string|null $caller_number
 * @property string|null $employee
 * @property string|null $employee_estension
 * @property string|null $employee_id
 * @property int|null $recording_presence
 * @property string|null $recording
 * @property string|null $recording_wav
 * @property int|null $duration_call
 * @property int|null $duration_conversation
 * @property int|null $duration_waiting
 * @property string|null $call_date
 * @property int|null $call_timestamp
 * @property float|null dialogue_quality_score // from ai
 * @property string|null dialogue_quality_details //from ai
 * @property string|null call_card_link //from ai
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static Builder|EventAfterCall newModelQuery()
 * @method static Builder|EventAfterCall newQuery()
 * @method static Builder|EventAfterCall query()
 * @method static Builder|EventAfterCall searchByPhonesFromClients(array $clientsIDs)
 * @method static Builder|EventAfterCall whereCallDate($value)
 * @method static Builder|EventAfterCall whereCallId($value)
 * @method static Builder|EventAfterCall whereCallTimestamp($value)
 * @method static Builder|EventAfterCall whereCallerNumber($value)
 * @method static Builder|EventAfterCall whereCreatedAt($value)
 * @method static Builder|EventAfterCall whereDestination($value)
 * @method static Builder|EventAfterCall whereDurationCall($value)
 * @method static Builder|EventAfterCall whereDurationConversation($value)
 * @method static Builder|EventAfterCall whereDurationWaiting($value)
 * @method static Builder|EventAfterCall whereEmployee($value)
 * @method static Builder|EventAfterCall whereEmployeeEstension($value)
 * @method static Builder|EventAfterCall whereEmployeeId($value)
 * @method static Builder|EventAfterCall whereId($value)
 * @method static Builder|EventAfterCall whereNumberE164($value)
 * @method static Builder|EventAfterCall whereProjectId($value)
 * @method static Builder|EventAfterCall whereRecording($value)
 * @method static Builder|EventAfterCall whereRecordingPresence($value)
 * @method static Builder|EventAfterCall whereRecordingWav($value)
 * @method static Builder|EventAfterCall whereSchemeName($value)
 * @method static Builder|EventAfterCall whereStatus($value)
 * @method static Builder|EventAfterCall whereType($value)
 * @method static Builder|EventAfterCall whereUpdatedAt($value)
 * @method static Builder|EventAfterCall searchByCustomerPhone($term)
 * @method static EventAfterCallFactory factory(...$parameters)
 *
 * @see self::communicationRecord()
 * @property CommunicationRecord|MorphOne communicationRecord
 *
 * @mixin \Eloquent
 */
class EventAfterCall extends Model
{
    use HasFactory;

    public const OUTBOUND_VALUE = 'out';
    public const INBOUND_VALUE = 'in';

    public const STATUS_NO_ANSWER = 'NO ANSWER';
    public const STATUS_VOICEMAIL = 'VOICEMAIL';
    public const STATUS_ANSWERED = 'ANSWERED';

    public const MORPH_NAME = 'ringostat-event_after_call';

    public const TABLE = 'event_after_call';
    protected $table = self::TABLE;

    protected $connection = DbConnections::RINGOSTAT;

    protected $fillable = [
        'project_id',
        'call_id',
        'type',
//        'scheme_name',
        'status',
        'destination',
        'number_e164',
        'caller_number',
        'employee',
        'employee_estension',
        'employee_id',
        'recording_presence',
        'recording',
        'recording_wav',
        'duration_call',
        'duration_conversation',
        'duration_waiting',
        'call_date',
        'call_timestamp'
    ];

    protected static function newFactory(): EventAfterCallFactory
    {
        return EventAfterCallFactory::new();
    }

    public function isNoAnswer(): bool
    {
        return $this->status === self::STATUS_NO_ANSWER;
    }

    public function isVoicemail(): bool
    {
        return $this->status === self::STATUS_VOICEMAIL;
    }

    public function communicationRecord(): MorphOne
    {
        return $this->MorphOne(CommunicationRecord::class, 'entity');
    }

    public function scopeSearchByPhonesFromClients(Builder $query, array $clientsIDs)
    {
        $clientPhones = Phone::whereIn('client_id', $clientsIDs)->whereRaw('LENGTH(`value`) >= 5')->get(['id', 'value']);
        return $query->where(function (Builder $q) use ($clientPhones) {
            if ($clientPhones->isNotEmpty()) {
                $q->where(function ($q) use ($clientPhones) {
                    $q->orWhere(function (Builder $q) use ($clientPhones) {
                        $q->where('type', '=', 'in')->where(function ($q) use ($clientPhones) {
                            foreach ($clientPhones as $Phone) {
                                $q->orWhere('caller_number', 'LIKE', '%' . $Phone->value);
                            }
                        });
                    })->orWhere(function (Builder $q) use ($clientPhones) {
                        $q->where('type', '=', 'out')->where(function ($q) use ($clientPhones) {
                            foreach ($clientPhones as $Phone) {
                                $q->orWhere('destination', 'LIKE', '%' . $Phone->value);
                            }
                        });
                    });
                });
            } else {
                $q->where('id', 0);
            }
        });
    }

    public function scopeSearchByCustomerPhone(Builder $query, $term)
    {
        return $query->orWhere(function (Builder $q) use ($term) {
            $q->where('caller_number', 'LIKE', '%' . $term . '%')->where('type', '=', 'in');;
        })->orWhere(function (Builder $q) use ($term) {
            $q->where('destination', 'LIKE', '%' . $term . '%')->where('type', '=', 'out');
        });
    }

}
