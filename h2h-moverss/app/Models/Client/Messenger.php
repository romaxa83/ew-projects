<?php

namespace App\Models\Client;

use App\Events\ClientMessengerUpdated;
use App\Models\Client;
use Database\Factories\Clients\MessengerFactory;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\{Factories\HasFactory, SoftDeletes, Model};

/**
 * App\Models\Client\Messenger
 *
 * @property int $id
 * @property int $client_id
 * @property string $value
 * @property int $type_id
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Client\MessengerType|null $type
 * @method static \Illuminate\Database\Eloquent\Builder|Messenger newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Messenger newQuery()
 * @method static \Illuminate\Database\Query\Builder|Messenger onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Messenger query()
 * @method static \Illuminate\Database\Eloquent\Builder|Messenger whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Messenger whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Messenger whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Messenger whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Messenger whereTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Messenger whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Messenger whereValue($value)
 * @method static \Illuminate\Database\Query\Builder|Messenger withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Messenger withoutTrashed()
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read int|null $audits_count
 * @property-read Client|null $client
 * @mixin \Eloquent
 * @method static MessengerFactory factory(...$parameters)
 */
class Messenger extends Model implements Auditable
{
    use AuditableTrait;
    use HasFactory;
    use SoftDeletes;

    public const MORPH_NAME = 'client-messenger';

    public const TABLE = 'clients_messengers';
    protected $table = self::TABLE;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'value',
        'type_id'
    ];

    protected $with = ['type:id,icon'];
    protected $dispatchesEvents = [
        'saving' => ClientMessengerUpdated::class
    ];

    protected static function newFactory(): MessengerFactory
    {
        return MessengerFactory::new();
    }

    public function type()
    {
        return $this->hasOne(MessengerType::class, 'id', 'type_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
