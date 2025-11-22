<?php

namespace Core\Chat\Events;

use Core\Chat\Models\Conversation;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AllParticipantsClearedConversation
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(protected Conversation $conversation)
    {
    }

    public function getConversation(): Conversation
    {
        return $this->conversation;
    }
}
