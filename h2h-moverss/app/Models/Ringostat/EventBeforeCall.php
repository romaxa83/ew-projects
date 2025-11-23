<?php

namespace App\Models\Ringostat;

use App\Helpers\DbConnections;
use Database\Factories\Ringostat\EventBeforeCallFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Ringostat\EventBeforeCall
 *
 * @property int id
 * @property int|null project_id
 * @property string|null call_type
 * @property string|null destination
 * @property string|null number_e164
 * @property string|null callers_number
 * @property int|null call_date_microsecond
 * @property int|null employee_ringostat_id
 * @property int|null client_id
 * @property int|string call_date
 * @property int|string call_id
 * @property int|string extension_number
 * @property int|string responsible_employees
 * @property string|null from_event
 * @property \Illuminate\Support\Carbon|null created_at
 * @property \Illuminate\Support\Carbon|null updated_at
 * @method static Builder|EventBeforeCall newModelQuery()
 * @method static Builder|EventBeforeCall newQuery()
 * @method static Builder|EventBeforeCall query()
 * @method static Builder|EventBeforeCall searchByPhonesFromClients(array $clientsIDs)
 * @method static Builder|EventBeforeCall whereCallDate($value)
 * @method static Builder|EventBeforeCall whereCallId($value)
 * @method static Builder|EventBeforeCall whereCallTimestamp($value)
 * @method static Builder|EventBeforeCall whereCallerNumber($value)
 * @method static Builder|EventBeforeCall whereCreatedAt($value)
 * @method static Builder|EventBeforeCall whereDestination($value)
 * @method static Builder|EventBeforeCall whereDurationCall($value)
 * @method static Builder|EventBeforeCall whereDurationConversation($value)
 * @method static Builder|EventBeforeCall whereDurationWaiting($value)
 * @method static Builder|EventBeforeCall whereEmployee($value)
 * @method static Builder|EventBeforeCall whereEmployeeEstension($value)
 * @method static Builder|EventBeforeCall whereEmployeeId($value)
 * @method static Builder|EventBeforeCall whereId($value)
 * @method static Builder|EventBeforeCall whereNumberE164($value)
 * @method static Builder|EventBeforeCall whereProjectId($value)
 * @method static Builder|EventBeforeCall whereRecording($value)
 * @method static Builder|EventBeforeCall whereRecordingPresence($value)
 * @method static Builder|EventBeforeCall whereRecordingWav($value)
 * @method static Builder|EventBeforeCall whereSchemeName($value)
 * @method static Builder|EventBeforeCall whereStatus($value)
 * @method static Builder|EventBeforeCall whereType($value)
 * @method static Builder|EventBeforeCall whereUpdatedAt($value)
 * @method static Builder|EventBeforeCall searchByCustomerPhone($term)
 * @method static EventBeforeCallFactory factory(...$parameters)
 * @mixin \Eloquent
 */
class EventBeforeCall extends Model
{
    use HasFactory;

    public const TABLE = 'event_before_call';
    protected $table = self::TABLE;

    protected $connection = DbConnections::RINGOSTAT;

    protected $fillable = [
        'project_id',
        'call_type',
        'call_date_microsecond',
        'destination',
        'number_e164',
        'callers_number',
        'client_id',
        'call_date',
        'call_id',
        'extension_number',
        'responsible_employees',
        'from_event',
    ];

    protected static function newFactory(): EventBeforeCallFactory
    {
        return EventBeforeCallFactory::new();
    }
}
