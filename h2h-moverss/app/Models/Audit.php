<?php

namespace App\Models;

use App\Helpers\DbConnections;
use App\Services\Audit\NormalizeDetailsService;
use App\Services\Audit\NormalizeEventService;
use App\User;
use Database\Factories\Audits\AuditFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * App\Models\Audit
 *
 * @property int $id
 * @property string|null $user_type
 * @property int|null $user_id
 * @property int|null $order_id
 * @property int|null $client_id
 * @property string $event
 * @property string $auditable_type
 * @property int $auditable_id
 * @property string|null $old_values
 * @property string|null $new_values
 * @property string|null $url
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property string|null $tags
 * @property string|null dispatch_truck_at
 * @property bool is_show_to_log
 * @property int|null division_id
 * @property bool is_client_activity  // является ли клиент инициатором события
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read User|null $user
 * @property-read Client|null $client
 * @method static \Illuminate\Database\Eloquent\Builder|Audit newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Audit newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Audit query()
 * @method static \Illuminate\Database\Eloquent\Builder|Audit whereAuditableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Audit whereAuditableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Audit whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Audit whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Audit whereEvent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Audit whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Audit whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Audit whereNewValues($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Audit whereOldValues($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Audit whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Audit whereTags($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Audit whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Audit whereUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Audit whereUserAgent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Audit whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Audit whereUserType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Audit whereDispatchTruckAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Audit whereIsClientActivity($value)
 * @mixin \Eloquent
 * @method static AuditFactory factory(...$parameters)
 */
class Audit extends Model
{
    use HasFactory;

    protected $connection = DbConnections::AUDIT;

    public const TABLE = 'audits';
    protected $table = self::TABLE;

    public const EVENT_CREATED = 'created';
    public const EVENT_DELETED = 'deleted';
    public const EVENT_UPDATED = 'updated';
    public const EVENT_CLONED = 'cloned';
    public const EVENT_SYNC = 'sync';

    protected $fillable = [
        'order_id',
        'dispatch_truck_at',
        'old_values',
        'new_values',
        'event',
        'is_client_activity',
        'auditable_type'
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'is_client_activity' => 'boolean',
        'is_show_to_log' => 'boolean',
    ];

    protected static function newFactory(): AuditFactory
    {
        return AuditFactory::new();
    }

    public function auditable()
    {
        return $this->morphTo('auditable');
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function client(): HasOne
    {
        return $this->hasOne(Client::class, 'id', 'client_id');
    }

    /**
     * test @see \Tests\Unit\Models\Audits\AuditTest
    */
    public function getPrettyValues(): array
    {
        $oldValues = $this->old_values;
        $newValues = $this->new_values;

        $tmp = [];

        if(!empty($oldValues)) {
            foreach($oldValues as $field => $value) {
                $tmp[] = [
                    'field' => $field,
                    'new' => $newValues[$field] ?? null,
                    'old' => $value,
                ];
            }
        }
        if(empty($tmp) && !empty($newValues)) {
            foreach($newValues as $field => $value) {
                $tmp[] = [
                    'field' => $field,
                    'new' => $value,
                    'old' => null,
                ];
            }
        }

        return $tmp;
    }

    public function isAttachment(): bool
    {
        return $this->auditable_type == Attachment::MORPH_NAME;
    }

    public function isEventDeleted(): bool
    {
        return $this->event == 'deleted';
    }
}
