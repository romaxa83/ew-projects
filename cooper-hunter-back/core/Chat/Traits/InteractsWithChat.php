<?php

namespace Core\Chat\Traits;

use Core\Chat\Events\ParticipantJoined;
use Core\Chat\Events\ParticipantLeft;
use Core\Chat\Exceptions\InvalidDirectMessageNumberOfParticipants;
use Core\Chat\Models\Conversation;
use Core\Chat\Models\Participation;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait InteractsWithChat
{
    public function conversations(): HasManyThrough|Conversation
    {
        return $this->hasManyThrough(
            Conversation::class,
            Participation::class,
            'messageable_id',
            'id',
            'id',
            'conversation_id',
        )->where('messageable_type', $this->getMorphClass());
    }

    /**
     * @throws InvalidDirectMessageNumberOfParticipants
     */
    public function joinConversation(Conversation $conversation): void
    {
        if ($conversation->isDirectMessage() && $conversation->participants()->count() === 2) {
            throw new InvalidDirectMessageNumberOfParticipants();
        }

        $participation = Participation::firstOrNew(
            [
                'messageable_id' => $this->getKey(),
                'messageable_type' => $this->getMorphClass(),
                'conversation_id' => $conversation->getKey(),
            ]
        );

        if ($participation->exists) {
            return;
        }

        event(new ParticipantJoined($conversation, $this));

        $this->participation()->save($participation);
    }

    public function participation(): MorphMany|Participation
    {
        return $this->morphMany(Participation::class, 'messageable');
    }

    public function leaveConversation(Conversation $conversation): void
    {
        $deleted = $this->participation()->where(
            [
                'messageable_id' => $this->getKey(),
                'messageable_type' => $this->getMorphClass(),
                'conversation_id' => $conversation->getKey(),
            ]
        )->delete();

        if ($deleted) {
            event(new ParticipantLeft($conversation, $this));
        }
    }
}
