<?php

namespace App\Models\Client;

use App\Events\ClientEmailUpdated;
use App\Models\Client;
use Database\Factories\Clients\EmailFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

/**
 * App\Models\Client\Email
 *
 * @property int $id
 * @property int $client_id
 * @property int $is_primary
 * @property int $sort
 * @property string $value
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @method static Builder|Email newModelQuery()
 * @method static Builder|Email newQuery()
 * @method static \Illuminate\Database\Query\Builder|Email onlyTrashed()
 * @method static Builder|Email query()
 * @method static Builder|Email whereClientId($value)
 * @method static Builder|Email whereCreatedAt($value)
 * @method static Builder|Email whereDeletedAt($value)
 * @method static Builder|Email whereId($value)
 * @method static Builder|Email whereIsPrimary($value)
 * @method static Builder|Email whereSort($value)
 * @method static Builder|Email whereUpdatedAt($value)
 * @method static Builder|Email whereValue($value)
 * @method static \Illuminate\Database\Query\Builder|Email withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Email withoutTrashed()
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read int|null $audits_count
 * @property-read Client|null $client
 * @method static EmailFactory factory(...$parameters)
 * @mixin \Eloquent
 */
class Email extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait;
    use SoftDeletes;

    public const MORPH_NAME = 'client-email';

    public const TABLE = 'clients_emails';
    protected $table = self::TABLE;

    protected $dates = [
        'deleted_at'
    ];

    protected $fillable = [
        'value',
        'is_primary'
    ];
    protected $dispatchesEvents = [
        'saving' => ClientEmailUpdated::class
    ];

    protected static function newFactory(): EmailFactory
    {
        return EmailFactory::new();
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
