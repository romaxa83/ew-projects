<?php

namespace App\Models\Communications;

use App\Enums\Communications\ConversationContactType;
use App\User;
use Database\Factories\Communications\ConversationMarkFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * App\Models\Communications\ConversationMark
 *
 * @property int $id
 * @property int|null $client_id
 * @property string|null $contact_type
 * @property string|null $contact_value
 * @property int $user_id
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read int|null $audits_count
 * @method static \Illuminate\Database\Eloquent\Builder|ConversationMark newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ConversationMark newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ConversationMark query()
 * @method static \Illuminate\Database\Eloquent\Builder|ConversationMark whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ConversationMark whereContactType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ConversationMark whereContactValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ConversationMark whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ConversationMark whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ConversationMark whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ConversationMark whereUserId($value)
 * @property-read \App\Models\Client|null $client
 * @property string $type
 * @property-read User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|ConversationMark whereType($value)
 * @method static ConversationMarkFactory factory(...$parameters)
 * @mixin \Eloquent
 */
class ConversationMark extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait;

    public const MORPH_NAME = 'conversations-mark';

    public const TYPE_READ = 'read';

    public const TABLE = 'conversations_marks';
    protected $table = self::TABLE;

    protected $fillable = [
        'client_id',
        'type',
        'contact_type',
        'contact_value',
        'user_id'
    ];

    public function contactTypeIsPhone(): bool
    {
        return $this->contact_type === ConversationContactType::Phone();
    }

    public function contactTypeIsEmail(): bool
    {
        return $this->contact_type === ConversationContactType::Email();
    }

    protected static function newFactory(): ConversationMarkFactory
    {
        return ConversationMarkFactory::new();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
