<?php

namespace App\Models\Client;

use Database\Factories\Clients\MessengerTypeFactory;
use Illuminate\Database\Eloquent\{Factories\HasFactory, Model, SoftDeletes};
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * App\Models\Client\MessengerType
 *
 * @property int $id
 * @property string $title
 * @property string|null $icon
 * @property int $sort
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|MessengerType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MessengerType newQuery()
 * @method static \Illuminate\Database\Query\Builder|MessengerType onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|MessengerType query()
 * @method static \Illuminate\Database\Eloquent\Builder|MessengerType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MessengerType whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MessengerType whereIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MessengerType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MessengerType whereSort($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MessengerType whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MessengerType whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|MessengerType withTrashed()
 * @method static \Illuminate\Database\Query\Builder|MessengerType withoutTrashed()
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read int|null $audits_count
 * @mixin \Eloquent
 * @method static MessengerTypeFactory factory(...$parameters)
 */
class MessengerType extends Model implements Auditable
{
    use AuditableTrait;
    use HasFactory;
    use SoftDeletes;

    public const TABLE = 'clients_messengers_types';
    protected $table = self::TABLE;

    // FIXME Move to messengers_types
    protected $dates = ['deleted_at'];

    protected static function newFactory(): MessengerTypeFactory
    {
        return MessengerTypeFactory::new();
    }
}
