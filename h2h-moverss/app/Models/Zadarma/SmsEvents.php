<?php

namespace App\Models\Zadarma;

use App\Models\Client\Phone;
use Database\Factories\Zadarma\CallEventFactory;
use Database\Factories\Zadarma\SmsEventFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Zadarma\SmsEvents
 *
 * @property int $id
 * @property string|null $pbx_id
 * @property int|null $inbound
 * @property string|null $caller_id
 * @property string|null $caller_did
 * @property string|null $text
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|SmsEvents newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SmsEvents newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SmsEvents query()
 * @method static \Illuminate\Database\Eloquent\Builder|SmsEvents whereCallerDid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SmsEvents whereCallerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SmsEvents whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SmsEvents whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SmsEvents whereInbound($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SmsEvents wherePbxId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SmsEvents whereText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SmsEvents whereUpdatedAt($value)
 * @method static Builder|SmsEvents searchByCustomerPhone($term)
 * @method static Builder|SmsEvents searchByPhonesFromClients(array $clientsIDs)
 * @method static SmsEventFactory factory(...$parameters)
 * @mixin \Eloquent
 */
class SmsEvents extends Model
{
    use HasFactory;

    public const MORPH_NAME = 'zadarma-sms-event';

    protected $table = 'zadarma_sms';
    protected $fillable = [
        'pbx_id', 'inbound', 'caller_id', 'caller_did', 'text'
    ];

    protected static function newFactory(): SmsEventFactory
    {
        return SmsEventFactory::new();
    }

    public function scopeSearchByCustomerPhone(Builder $query, $term)
    {
        return $query->orWhere(function (Builder $q) use ($term) {
            $q->where('caller_id', 'LIKE', '%' . $term . '%')->where('inbound', '=','1');;
        })->orWhere(function (Builder $q) use ($term) {
            $q->where('caller_did', 'LIKE', '%' . $term . '%')->where('inbound', '<>','1');
        });
    }

    public function scopeSearchByPhonesFromClients(Builder $query, array $clientsIDs)
    {
        $clientPhones = Phone::whereIn('client_id', $clientsIDs)->whereRaw('LENGTH(`value`) >= 5')->get(['id', 'value']);
        return $query->where(function (Builder $q) use ($clientPhones) {
            if ($clientPhones->isNotEmpty()) {
                $q->where(function ($q) use ($clientPhones) {
                    $q->orWhere(function (Builder $q) use ($clientPhones) {
                        $q->where('inbound', '=', '1')->where(function ($q) use ($clientPhones) {
                            foreach ($clientPhones as $Phone) {
                                $q->orWhere('caller_id', 'LIKE', '%' . $Phone->value);
                            }
                        });
                    })->orWhere(function (Builder $q) use ($clientPhones) {
                        $q->where('inbound', '<>', '1')->where(function ($q) use ($clientPhones) {
                            foreach ($clientPhones as $Phone) {
                                $q->orWhere('caller_did', 'LIKE', '%' . $Phone->value);
                            }
                        });
                    });
                });
            } else {
                $q->where('id', 0);
            }
        });
    }

}
