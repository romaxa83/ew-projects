<?php

namespace Core\Chat\Events;

use Core\Chat\Models\Message;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AllParticipantsDeletedMessage
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(protected Message $message)
    {
    }

    public function getMessage(): Message
    {
        return $this->message;
    }
}
