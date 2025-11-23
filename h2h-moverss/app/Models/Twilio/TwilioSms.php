<?php

namespace App\Models\Twilio;

use App\Helpers\DbConnections;
use App\Models\Attachment;
use App\Models\Client\Phone;
use Database\Factories\Twilio\TwilioFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * App\Models\Twilio\TwilioSms
 *
 * @property int id
 * @property int|null division
 * @property string sid
 * @property string|null direction
 * @property string|null from
 * @property string|null to
 * @property string|null body
 * @property string|null misc
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read int|null $audits_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Twilio\TwilioSmsStatus[] $statuses
 * @property-read int|null $statuses_count
 * @method static Builder|TwilioSms newModelQuery()
 * @method static Builder|TwilioSms newQuery()
 * @method static Builder|TwilioSms query()
 * @method static Builder|TwilioSms searchByClientPhones(array $clientsIDs)
 * @method static Builder|TwilioSms searchByPhonesFromArray(array $clientPhones)
 * @method static Builder|TwilioSms whereBody($value)
 * @method static Builder|TwilioSms whereCreatedAt($value)
 * @method static Builder|TwilioSms whereDirection($value)
 * @method static Builder|TwilioSms whereDivision($value)
 * @method static Builder|TwilioSms whereFrom($value)
 * @method static Builder|TwilioSms whereId($value)
 * @method static Builder|TwilioSms whereMisc($value)
 * @method static Builder|TwilioSms whereSid($value)
 * @method static Builder|TwilioSms whereTo($value)
 * @method static Builder|TwilioSms whereUpdatedAt($value)
 * @method static TwilioFactory factory(...$parameters)
 * @mixin \Eloquent
 */
class TwilioSms extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory;

    public const OUTBOUND_VALUE = 'outbound-api';
    public const INBOUND_VALUE = 'inbound';
    public const MORPH_NAME = 'twilio-sms';

    protected $table = 'twilio_sms';

    protected $connection = DbConnections::DEFAULT;

    protected $fillable = [
        'sid',
        'division',
        'direction',
        'from',
        'to',
        'body',
        'misc',
        'media_urls'
    ];

    protected $casts = [
        'misc' => 'array',
    ];

    protected static function newFactory(): TwilioFactory
    {
        return TwilioFactory::new();
    }

    public function statuses()
    {
        return $this->hasMany(TwilioSmsStatus::class, 'sid', 'sid');
    }

    public function medias()
    {
        return $this->morphMany(Attachment::class, 'entity');
    }

    public function getMediaUrls(): array
    {
        $tmp = [];
        foreach ($this->medias as $media) {
            /** @var $media Attachment */
            if($url = $media->getUrl()){
                $tmp[] = $url;
            }
        }

        return $tmp;
    }

    public function scopeSearchByPhonesFromArray($query, array $clientPhones) {
        return $query->where(function (Builder $q) use ($clientPhones) {
                $q->where(function ($q) use ($clientPhones) {
                    $q->where('direction', 'inbound')->where(function ($q) use ($clientPhones) {
                        foreach ($clientPhones as $Phone) {
                            $q->orWhere('from', 'LIKE', '%' . $Phone);
                        }
                    });
                });
                $q->orWhere(function ($q) use ($clientPhones) {
                    $q->where('direction', 'outbound-api')
                        ->where(function ($q) use ($clientPhones) {
                            foreach ($clientPhones as $Phone) {
                                $q->orWhere('to', 'LIKE', '%' . $Phone);
                            }
                        });
                });
        });
    }

    public function scopeSearchByClientPhones($query, array $clientsIDs)
    {
        $clientPhones = Phone::whereIn('client_id', $clientsIDs)->whereRaw('LENGTH(`value`) >= 5')->get(['id', 'value']);
        return $query->where(function (Builder $q) use ($clientPhones) {
            if ($clientPhones->isNotEmpty()) {
                $q->where(function ($q) use ($clientPhones) {
                    $q->where('direction', 'inbound')->where(function ($q) use ($clientPhones) {
                        foreach ($clientPhones as $Phone) {
                            $q->orWhere('from', 'LIKE', '%' . $Phone->value);
                        }
                    });
                });
                $q->orWhere(function ($q) use ($clientPhones) {
                    $q->where('direction', 'outbound-api')
                        ->where(function ($q) use ($clientPhones) {
                            foreach ($clientPhones as $Phone) {
                                $q->orWhere('to', 'LIKE', '%' . $Phone->value);
                            }
                        });
                });
            } else {
                $q->where('id', 0);
            }
        });
    }

}
