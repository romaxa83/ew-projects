<?php

namespace Core\Chat\Models;

use Core\Chat\Contracts\Messageable;
use Core\Chat\Database\Factories\ParticipationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @see Participation::conversation()
 * @property-read Conversation conversation
 *
 * @see Participation::messageable()
 * @property-read Messageable messageable
 *
 * @method static ParticipationFactory factory(...$parameters)
 */
class Participation extends BaseChatModel
{
    use HasFactory;

    public const TABLE = 'chat_participation';

    protected $table = self::TABLE;

    protected $fillable = [
        'conversation_id',
    ];

    protected static function newFactory(): ParticipationFactory
    {
        return ParticipationFactory::new();
    }

    public function conversation(): BelongsTo|Conversation
    {
        return $this->belongsTo(Conversation::class, 'conversation_id');
    }

    public function messageable(): MorphTo|Messageable
    {
        return $this->morphTo()->with('participation');
    }
}
