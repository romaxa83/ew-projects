<?php

namespace Core\Chat\Contracts;

use Core\Chat\Models\Conversation;
use Core\Chat\Models\Participation;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;

/**
 * @see Messageable::participation()
 * @property-read Participation|null participation
 *
 * @see Messageable::participation()
 * @property-read Collection<Conversation> conversations
 */
interface Messageable
{
    public function getMorphClass();

    public function getKey();

    public function getName(): string;

    public function participation(): MorphMany|Participation;

    public function conversations(): HasManyThrough|Conversation;

    public function joinConversation(Conversation $conversation): void;

    public function leaveConversation(Conversation $conversation): void;
}
