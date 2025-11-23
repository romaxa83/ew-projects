<?php

namespace App\Models\Zadarma;

use App\Models\Client\Phone;
use App\Models\Employee;
use Database\Factories\Zadarma\CallEventFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Arr;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * App\Models\Zadarma\CallsEvents
 *
 * @property int id
 * @property string|null event
 * @property string|null pbx_id
 * @property string|null call_start pbx timezone
 * @property string|null pbx_call_id
 * @property string|null caller_id
 * @property string|null destination
 * @property string|null called_did
 * @property int|null internal
 * @property int|null duration
 * @property string|null disposition
 * @property int|null status_code
 * @property int|null is_recorded
 * @property string|null call_id_with_rec
 * @property int|null client_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read int|null $audits_count
 * @property-read \App\Models\Employee\PbxData|null $destinationPbxData
 * @property-read Employee|null $internalEmployee
 * @property-read \App\Models\Employee\PbxData|null $internalPbxData
 * @property-read Employee|null $pbxEmployee
 * @method static Builder|CallsEvents newModelQuery()
 * @method static Builder|CallsEvents newQuery()
 * @method static Builder|CallsEvents query()
 * @method static Builder|CallsEvents searchByCustomerPhone($term)
 * @method static Builder|CallsEvents searchByPhonesFromArray(array $clientPhones)
 * @method static Builder|CallsEvents searchByPhonesFromClients(array $clientsIDs)
 * @method static Builder|CallsEvents whereCallIdWithRec($value)
 * @method static Builder|CallsEvents whereCallStart($value)
 * @method static Builder|CallsEvents whereCalledDid($value)
 * @method static Builder|CallsEvents whereCallerId($value)
 * @method static Builder|CallsEvents whereCreatedAt($value)
 * @method static Builder|CallsEvents whereDestination($value)
 * @method static Builder|CallsEvents whereDisposition($value)
 * @method static Builder|CallsEvents whereDuration($value)
 * @method static Builder|CallsEvents whereEvent($value)
 * @method static Builder|CallsEvents whereId($value)
 * @method static Builder|CallsEvents whereInternal($value)
 * @method static Builder|CallsEvents whereIsRecorded($value)
 * @method static Builder|CallsEvents wherePbxCallId($value)
 * @method static Builder|CallsEvents wherePbxId($value)
 * @method static Builder|CallsEvents whereStatusCode($value)
 * @method static Builder|CallsEvents whereUpdatedAt($value)
 * @method static CallEventFactory factory(...$parameters)
 * @mixin \Eloquent
 */
class CallsEvents extends Model implements Auditable
{
    use AuditableTrait;
    use \Awobaz\Compoships\Compoships;
    use HasFactory;

    public const MORPH_NAME = 'zadarma-calls-event';

    public const EVENT_NOTIFY_END = 'NOTIFY_END';
    public const EVENT_NOTIFY_OUT_END = 'NOTIFY_OUT_END';
    public const EVENT_NOTIFY_OUT_START = 'NOTIFY_OUT_START';
    public const DISPOSITION_ANSWERED = 'answered';
    public const DISPOSITION_CANCEL = 'cancel';
    public const DISPOSITION_VOICEMAIL = 'voicemail';

    public const TABLE = 'zadarma_calls_end';
    protected $table = self::TABLE;

    protected $fillable = [
        'event',
        'pbx_id',
        'call_start',
        'pbx_call_id',
        'caller_id',
        'destination',
        'called_did',
        'internal',
        'duration',
        'disposition',
        'status_code',
        'is_recorded',
        'call_id_with_rec',
        'client_id'
    ];

    protected static function newFactory(): CallEventFactory
    {
        return CallEventFactory::new();
    }

    // лучше убрать, cast creates Carbon in project Timezone
//    protected $casts = [
//        'call_start' => 'datetime',
//    ];

    public function transformAudit(array $data): array
    {

        if (Arr::has($data, 'new_values.event') &&
            ($data['new_values']['event'] == 'NOTIFY_ANSWER' || $data['new_values']['event'] == 'NOTIFY_OUT_START')) {
            $Employee = $this->getRelationValue('internalEmployee');
            if ($Employee) {
                $data['user_id'] = $Employee->auth_user_id;
                $data['user_type'] = 'App\\User';
            }
        }

        return $data;
    }


    public function scopeSearchByPhonesFromArray(Builder $query, array $clientPhones)
    {
        return $query->where(function (Builder $q) use ($clientPhones) {
            $q->where(function ($q) use ($clientPhones) {
                $q->orWhere(function (Builder $q) use ($clientPhones) {
                    $q->where('pbx_call_id', 'LIKE', 'in_%')->where(function ($q) use ($clientPhones) {
                        foreach ($clientPhones as $Phone) {
                            $q->orWhere('caller_id', 'LIKE', '%' . $Phone);
                        }
                    });
                })->orWhere(function (Builder $q) use ($clientPhones) {
                    $q->where('pbx_call_id', 'LIKE', 'out_%')->where(function ($q) use ($clientPhones) {
                        foreach ($clientPhones as $Phone) {
                            $q->orWhere('destination', 'LIKE', '%' . $Phone);
                        }
                    });
                });
            });
        });
    }

    public function scopeSearchByPhonesFromClients(Builder $query, array $clientsIDs)
    {
        $clientPhones = Phone::whereIn('client_id', $clientsIDs)->whereRaw('LENGTH(`value`) >= 5')->get(['id', 'value']);
        return $query->where(function (Builder $q) use ($clientPhones) {
            if ($clientPhones->isNotEmpty()) {
                $q->where(function ($q) use ($clientPhones) {
                    $q->orWhere(function (Builder $q) use ($clientPhones) {
                        $q->where('pbx_call_id', 'LIKE', 'in_%')->where(function ($q) use ($clientPhones) {
                            foreach ($clientPhones as $Phone) {
                                $q->orWhere('caller_id', 'LIKE', '%' . $Phone->value);
                            }
                        });
                    })->orWhere(function (Builder $q) use ($clientPhones) {
                        $q->where('pbx_call_id', 'LIKE', 'out_%')->where(function ($q) use ($clientPhones) {
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
            $q->where('caller_id', 'LIKE', '%' . $term . '%')->where('pbx_call_id', 'LIKE', 'in_%');;
        })->orWhere(function (Builder $q) use ($term) {
            $q->where('destination', 'LIKE', '%' . $term . '%')->where('pbx_call_id', 'LIKE', 'out_%');
        });
    }

    public function internalEmployee(): HasOne
    {
        return $this->hasOne(Employee::class, 'pbx_ext', 'internal');
    }

    public function pbxEmployee(): HasOne
    {
        return $this->hasOne(Employee::class, 'pbx_ext', 'destination');
    }

    public function destinationPbxData()
    {
        return $this->hasOne(Employee\PbxData::class, ['pbx_id', 'pbx_ext'], ['pbx_id', 'destination']);
    }

    public function internalPbxData()
    {
        return $this->hasOne(Employee\PbxData::class, ['pbx_id', 'pbx_ext'], ['pbx_id', 'internal']);
    }

}
