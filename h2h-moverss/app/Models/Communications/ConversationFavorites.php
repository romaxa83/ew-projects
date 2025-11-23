<?php

namespace App\Models\Communications;

use App\Enums\Communications\ConversationContactType;
use App\User;
use Database\Factories\Communications\ConversationFavoritesFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * App\Models\Communications\ConversationFavorites
 *
 * @property int id
 * @property int starred
 * @property int|null client_id
 * @property string|null contact_type
 * @property string|null contact_value
 * @property int user_id
 * @property int|null communication_rec_id
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read int|null $audits_count
 * @property-read User|null $user
 * @method static Builder|ConversationFavorites newModelQuery()
 * @method static Builder|ConversationFavorites newQuery()
 * @method static Builder|ConversationFavorites query()
 * @method static Builder|ConversationFavorites whereClientId($value)
 * @method static Builder|ConversationFavorites whereContactType($value)
 * @method static Builder|ConversationFavorites whereContactValue($value)
 * @method static Builder|ConversationFavorites whereCreatedAt($value)
 * @method static Builder|ConversationFavorites whereId($value)
 * @method static Builder|ConversationFavorites whereStarred($value)
 * @method static Builder|ConversationFavorites whereUpdatedAt($value)
 * @method static Builder|ConversationFavorites whereUserId($value)
 * @method static ConversationFavoritesFactory factory(...$parameters)d
 * @mixin \Eloquent
 */
class ConversationFavorites extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait;

    public const MORPH_NAME = 'conversations-favorites';

    public const TABLE = 'conversations_favorites';
    protected $table = self::TABLE;

    protected $fillable = [
        'client_id',
        'starred',
        'contact_type',
        'contact_value',
        'user_id',
        'communication_rec_id'
    ];

    protected $casts = [
        'starred' => 'boolean',
    ];

    protected static function newFactory(): ConversationFavoritesFactory
    {
        return ConversationFavoritesFactory::new();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeByPhone($query)
    {
        return $query->where('contact_type', ConversationContactType::Phone());
    }
}
