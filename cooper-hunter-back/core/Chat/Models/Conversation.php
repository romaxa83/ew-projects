<?php

namespace Core\Chat\Models;

use Carbon\Carbon;
use Core\Chat\Contracts\Messageable;
use Core\Chat\Database\Factories\ConversationFactory;
use Core\Chat\Exceptions\DeletingConversationWithParticipantsException;
use Core\Chat\Facades\Chat;
use Core\Chat\Filters\Conversations\ConversationFilter;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Image\Exceptions\InvalidManipulation;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @property int id
 * @property bool direct_message
 * @property string title
 * @property ?string description
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 * @see Conversation::participants()
 * @property-read Collection|Participation[] participants
 *
 * @method static ConversationFactory factory(...$parameters)
 */
class Conversation extends BaseChatModel implements HasMedia
{
    use Filterable;
    use HasFactory;
    use InteractsWithMedia;

    public const TABLE = 'chat_conversation';
    public const MEDIA_COLLECTION_NAME = 'avatar';
    public const MORPH_NAME = 'chat_conversation';

    protected $table = self::TABLE;

    protected $fillable = [
        'direct_message',
        'title',
        'description',
    ];

    protected $casts = [
        'direct_message' => 'boolean',
    ];

    protected static function newFactory(): ConversationFactory
    {
        return ConversationFactory::new();
    }

    public function modelFilter(): string
    {
        return ConversationFilter::class;
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::MEDIA_COLLECTION_NAME)
            ->singleFile()
            ->acceptsMimeTypes(
                [
                    'image/jpeg',
                    'image/jpg',
                    'image/png',
                    'image/bmp',
                    'image/gif',
                    'image/webp',
                ]
            );
    }

    /**
     * @throws InvalidManipulation
     */
    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion(self::MEDIA_COLLECTION_NAME)
            ->width(100)
            ->height(100);
    }

    /**
     * @throws DeletingConversationWithParticipantsException
     */
    public function delete(): ?bool
    {
        if ($this->participants()->count()) {
            throw new DeletingConversationWithParticipantsException();
        }

        return parent::delete();
    }

    public function participants(): HasMany|Participation
    {
        return $this->hasMany(Participation::class);
    }

    public function getParticipants(): Collection
    {
        return $this->participants->loadMissing('messageable');
    }

    public function lastMessage(): HasOne|Message
    {
        return $this->hasOne(Message::class)
            ->orderByDesc(Chat::getMessageTable() . '.id');
    }

    public function messages(): HasMany|Message
    {
        return $this->hasMany(Message::class, 'conversation_id');
    }

    public function notifications(): HasMany|MessageNotification
    {
        return $this->hasMany(MessageNotification::class, 'conversation_id');
    }

    public function participantFromSender(Messageable $sender): ?Participation
    {
        return $this->participants()->where(
            [
                'conversation_id' => $this->getKey(),
                'messageable_id' => $sender->getKey(),
                'messageable_type' => $sender->getMorphClass(),
            ]
        )->first();
    }

    /**
     * @param Messageable|array<Messageable> $participants
     */
    public function removeParticipant(Messageable|array $participants): static
    {
        if (is_array($participants)) {
            foreach ($participants as $participant) {
                $participant->leaveConversation($this);
            }

            return $this;
        }

        $participants->leaveConversation($this);

        return $this;
    }

    /**
     * @param Messageable|iterable<Messageable> $participants
     */
    public function addParticipants(Messageable|array $participants): static
    {
        if ($participants instanceof Messageable) {
            $participants = func_get_args();
        }

        foreach ($participants as $participant) {
            $participant->joinConversation($this);
        }

        return $this;
    }

    public function isDirectMessage(): bool
    {
        return $this->direct_message;
    }
}
