<?php

namespace App\Models\Communications;

use App\Enums\Communications\Type;
use App\Helpers\DbConnections;
use App\ModelFilters\Communications\RecordFilter;
use App\Models\Client;
use App\Models\Mailbox\Gmail\Message;
use App\Models\Order;
use App\Models\Ringostat\EventAfterCall;
use App\Models\Twilio\TwilioSms;
use App\Models\Zadarma\CallsEvents;
use App\Models\Zadarma\SmsEvents;
use Database\Factories\Communications\CommunicationRecordFactory;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\Communications\CommunicationRecord
 *
 * @property int id
 * @property string entity_type
 * @property int entity_id
 * @property int|null client_id
 * @property int|null order_id
 * @property array client_ids
 * @property int|null division_id
 * @property Type type
 * @property bool is_answered
 * @property string|null channel_contact
 * @property Carbon|null sort_at       // дата по которой сортируются и фильтруется данные
 * @property Carbon|null created_at
 * @property Carbon|null updated_at
 * @property-read Model|\Eloquent $entity
 * @method static \Illuminate\Database\Eloquent\Builder|CommunicationRecord newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CommunicationRecord newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CommunicationRecord query()
 * @method static \Illuminate\Database\Eloquent\Builder|CommunicationRecord whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommunicationRecord whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommunicationRecord whereDivisionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommunicationRecord whereEntityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommunicationRecord whereEntityType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommunicationRecord whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommunicationRecord whereIsAnswered($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommunicationRecord whereProvider($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommunicationRecord whereSortAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommunicationRecord whereStarred($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommunicationRecord whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommunicationRecord whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommunicationRecord whereChannelContact($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommunicationRecord whereClientIds($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommunicationRecord filter(array $input = [], $filter = null)
 * @method static \Illuminate\Database\Eloquent\Builder|CommunicationRecord paginateFilter($perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder|CommunicationRecord simplePaginateFilter($perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder|CommunicationRecord whereBeginsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|CommunicationRecord whereEndsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|CommunicationRecord whereLike($column, $value, $boolean = 'and')
 * @mixin \Eloquent
 *
 * @see self::client()
 * @property Client|HasOne client
 *
 * @method static CommunicationRecordFactory factory(...$parameters)
 */
class CommunicationRecord extends Model
{
    use HasFactory;
    use Filterable;

    public const TABLE = 'communication_records';
    protected $table = self::TABLE;

    protected $connection = DbConnections::DEFAULT;

    protected $fillable = [
        'entity_type',
        'entity_id',
        'client_id',
        'client_ids',
        'type',
        'is_answered',
        'created_at',
        'updated_at',
        'sort_at',
        'division_id',
        'channel_contact',
        'order_id',
    ];

    protected $casts = [
        'type' => Type::class,
        'is_answered' => 'boolean',
        'sort_at' => 'datetime',
        'client_ids' => 'array',
    ];

    protected static function newFactory(): CommunicationRecordFactory
    {
        return CommunicationRecordFactory::new();
    }

    public function modelFilter()
    {
        return $this->provideFilter(RecordFilter::class);
    }

    public function entity(): MorphTo
    {
        return $this->morphTo('entity');
    }

    public function client(): HasOne
    {
        return $this->hasOne(Client::class, 'id', 'client_id');
    }

    public function order(): HasOne
    {
        return $this->hasOne(Order::class, 'id', 'order_id');
    }

    public function isTwilioSms(): bool
    {
        return $this->entity_type === TwilioSms::MORPH_NAME;
    }

    public function isRingostatCall(): bool
    {
        return $this->entity_type === EventAfterCall::MORPH_NAME;
    }

    public function isZadarmaCall(): bool
    {
        return $this->entity_type === CallsEvents::MORPH_NAME;
    }

    public function isZadarmaSms(): bool
    {
        return $this->entity_type === SmsEvents::MORPH_NAME;
    }

    public function isClientActivity(): bool
    {
        return $this->entity_type === Client\Activity::MORPH_NAME;
    }

    public function isGmailMsg(): bool
    {
        return $this->entity_type === Message::MORPH_NAME;
    }
}
