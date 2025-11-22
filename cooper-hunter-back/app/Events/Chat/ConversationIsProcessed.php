<?php

namespace App\Events\Chat;

use Core\Chat\Contracts\Messageable;
use Core\Chat\Models\Conversation;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ConversationIsProcessed
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(protected Conversation $conversation, protected ?Messageable $processedBy = null)
    {
    }

    public function getConversation(): Conversation
    {
        return $this->conversation;
    }

    public function getProcessedBy(): ?Messageable
    {
        return $this->processedBy;
    }
}
