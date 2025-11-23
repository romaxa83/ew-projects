<?php

namespace App\Models\Client;

use App\Models\Client;
use Database\Factories\Clients\NoteFactory;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\{Factories\HasFactory, SoftDeletes, Model};
use App\Events\ClientNotesUpdated;
use App\User;

/**
 * App\Models\Client\Notes
 *
 * @property int $id
 * @property int $client_id
 * @property int $user_id
 * @property string $value
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|Notes newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Notes newQuery()
 * @method static \Illuminate\Database\Query\Builder|Notes onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Notes query()
 * @method static \Illuminate\Database\Eloquent\Builder|Notes whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notes whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notes whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notes whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notes whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notes whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notes whereValue($value)
 * @method static \Illuminate\Database\Query\Builder|Notes withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Notes withoutTrashed()
 * @property-read User|null $author
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read int|null $audits_count
 * @property-read Client|null $client
 * @mixin \Eloquent
 * @method static NoteFactory factory(...$parameters)
 */
class Notes extends Model implements Auditable
{
    use AuditableTrait;
    use HasFactory;
    use SoftDeletes;

    public const MORPH_NAME = 'client-notes';

    public const TABLE = 'clients_notes';
    protected $table = self::TABLE;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'value',
        'user_id'
    ];
    protected $dispatchesEvents = [
        'saving' => ClientNotesUpdated::class
    ];

    protected static function newFactory(): NoteFactory
    {
        return NoteFactory::new();
    }

    public function author()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
