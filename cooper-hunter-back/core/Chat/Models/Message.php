<?php

namespace Core\Chat\Models;

use BenSampo\Enum\Traits\CastsEnums;
use Carbon\Carbon;
use Core\Chat\Enums\MessageTypeEnum;
use Core\Chat\Filters\Messages\MessageFilter;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder;
use Spatie\Image\Exceptions\InvalidManipulation;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @property int id
 * @property string body
 * @property int participation_id
 * @property bool mark_seen
 * @property MessageTypeEnum type
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 * @see Message::participation()
 * @property-read Participation participation
 *
 * @see Message::conversation()
 * @property-read Conversation conversation
 *
 * @see Message::messageNotifications()
 * @property-read Collection|MessageNotification[] messageNotifications
 *
 * @method static self|Builder filter(array $attributes, string $filterClass = null)
 */
class Message extends BaseChatModel implements HasMedia
{
    use CastsEnums;
    use Filterable;
    use InteractsWithMedia;

    public const TABLE = 'chat_messages';

    public const MORPH_NAME = 'chat_message';

    protected $table = self::TABLE;

    protected $fillable = [
        'body',
        'participation_id',
        'type',
        'mark_seen',
    ];

    protected $touches = ['conversation'];

    protected $casts = [
        'type' => MessageTypeEnum::class,
    ];

    protected $appends = ['sender'];

    /**
     * @throws InvalidManipulation
     */
    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumbnail')
            ->width(300);
    }

    public function modelFilter(): string
    {
        return MessageFilter::class;
    }

    public function participation(): BelongsTo|Participation
    {
        return $this->belongsTo(Participation::class, 'participation_id');
    }

    public function getSenderAttribute()
    {
        $participantModel = $this->participation->messageable;

        if (!isset($participantModel)) {
            return null;
        }

        if (method_exists($participantModel, 'getParticipantDetails')) {
            return $participantModel->getParticipantDetails();
        }

        return $this->participation->messageable;
    }

    public function conversation(): BelongsTo|Conversation
    {
        return $this->belongsTo(Conversation::class, 'conversation_id');
    }

    public function messageNotifications(): HasMany|MessageNotification
    {
        return $this->hasMany(MessageNotification::class);
    }
}
