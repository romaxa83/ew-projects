<?php

namespace App\Models\Order;

use App\Models\Communications\CommunicationRecord;
use App\Models\Order;
use App\User;
use Database\Factories\Orders\NoteFactory;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\{Factories\HasFactory, Relations\HasOne, Relations\MorphOne, SoftDeletes, Model};

/**
 * App\Models\Order\Notes
 *
 * @property int id
 * @property int order_id
 * @property int user_id
 * @property int is_pinned
 * @property string|null visibility
 * @property string text
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read User|null $author
 * @property-read Order $order
 * @method static \Illuminate\Database\Eloquent\Builder|Notes newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Notes newQuery()
 * @method static \Illuminate\Database\Query\Builder|Notes onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Notes query()
 * @method static \Illuminate\Database\Eloquent\Builder|Notes whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notes whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notes whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notes whereIsPinned($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notes whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notes whereText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notes whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notes whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notes whereVisibility($value)
 * @method static \Illuminate\Database\Query\Builder|Notes withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Notes withoutTrashed()
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read int|null $audits_count
 * @mixin \Eloquent
 * @method static NoteFactory factory(...$parameters)
 *
 * @see self::communicationRecord()
 * @property CommunicationRecord|MorphOne communicationRecord
 */
class Notes extends Model implements Auditable
{
    use SoftDeletes;
    use AuditableTrait;
    use HasFactory;

    public const MORPH_NAME = 'order-note';

    public const TABLE = 'orders_notes';
    protected $table = self::TABLE;

    protected $dates = [
        'deleted_at'
    ];

    protected $casts = [
        'is_pinned' => 'boolean'
    ];

    protected static function newFactory(): NoteFactory
    {
        return NoteFactory::new();
    }

    public function author(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function order(): HasOne
    {
        return $this->hasOne(Order::class, 'id', 'order_id');
    }

    public function communicationRecord(): MorphOne
    {
        return $this->morphOne(CommunicationRecord::class, 'entity');
    }
}
