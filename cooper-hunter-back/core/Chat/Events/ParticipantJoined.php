<?php

namespace Core\Chat\Events;

use Core\Chat\Contracts\Messageable;
use Core\Chat\Models\Conversation;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ParticipantJoined
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(protected Conversation $conversation, protected Messageable $participant)
    {
    }

    public function getConversation(): Conversation
    {
        return $this->conversation;
    }

    public function getParticipant(): Messageable
    {
        return $this->participant;
    }
}
