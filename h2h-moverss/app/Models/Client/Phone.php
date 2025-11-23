<?php

namespace App\Models\Client;

use App\Events\ClientPhoneUpdated;
use App\Models\Client;
use Database\Factories\Clients\PhoneFactory;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\{Factories\HasFactory, SoftDeletes, Model};
use function GuzzleHttp\Psr7\str;

/**
 * App\Models\Client\Phone
 *
 * @property int $id
 * @property int $client_id
 * @property int $type_id
 * @property int $is_primary
 * @property int $sort
 * @property string $value
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|Phone newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Phone newQuery()
 * @method static \Illuminate\Database\Query\Builder|Phone onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Phone query()
 * @method static \Illuminate\Database\Eloquent\Builder|Phone whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Phone whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Phone whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Phone whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Phone whereIsPrimary($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Phone whereSort($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Phone whereTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Phone whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Phone whereValue($value)
 * @method static \Illuminate\Database\Query\Builder|Phone withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Phone withoutTrashed()
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read int|null $audits_count
 * @property-read Client|null $client
 * @method static PhoneFactory factory(...$parameters)
 * @mixin \Eloquent
 */
class Phone extends Model implements Auditable
{
    use AuditableTrait;
    use SoftDeletes;
    use HasFactory;

    public const MORPH_NAME = 'client-phone';

    public const TABLE = 'clients_phones';
    protected $table = self::TABLE;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'value',
        'type_id',
        'is_primary',
        'sort'
    ];

    protected $attributes = [
        'type_id' => 1,
    ];
    protected $dispatchesEvents = [
        'saving' => ClientPhoneUpdated::class
    ];

    public static function boot()
    {
        parent::boot();
        static::updating(function ($item) {
            $item->value = self::clearPhone($item->value);
        });
        static::creating(function ($item) {
            $item->value = self::clearPhone($item->value);
        });
    }

    protected static function newFactory(): PhoneFactory
    {
        return PhoneFactory::new();
    }

    /**
     * Remove non-digits and US code
     * @param $phone
     * @return array|string|string[]|null
     */
    public static function clearPhone($phone)
    {
        $cleared = preg_replace("/[^0-9]/", "", $phone);
        if ((strpos($phone, '+1') === 0 || strlen($cleared) == 11) && $cleared[0] == '1') {
            $cleared = substr($cleared, 1);
        }
        if (strlen($cleared) == 11 && $cleared[0] == '0') {
            $cleared = substr($cleared, 1);
        }

        return $cleared;
    }

    public static function getInternationalPhoneNumber($phonenumber, $countryCode)
    {
        $PhoneUtil = PhoneNumberUtil::getInstance();
        $NumberProto = $PhoneUtil->parse($phonenumber, $countryCode);
        return $PhoneUtil->format($NumberProto, PhoneNumberFormat::INTERNATIONAL);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

}
