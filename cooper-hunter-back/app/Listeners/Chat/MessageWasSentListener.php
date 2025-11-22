<?php

namespace App\Listeners\Chat;

use Core\Chat\Enums\ConversationUpdatedEventTypeEnum;
use Core\Chat\Events\MessageWasSent;
use Core\Chat\Models\Message;
use Core\Chat\Models\Participation;
use Illuminate\Database\Eloquent\Collection;

class MessageWasSentListener extends BaseChatEventListener
{
    public function handle(MessageWasSent $event): void
    {
        $this->notifyAllParticipants($event->getMessage());
    }

    protected function notifyAllParticipants(Message $message): void
    {
        $conversation = $message->conversation;
        $conversationId = $conversation->id;
        $messageId = $message->id;

        $eventType = ConversationUpdatedEventTypeEnum::MESSAGE_SENT();

        $conversation->participants()
            ->with('messageable')
            ->chunk(
                500,
                function (Collection $participants) use ($conversationId, $messageId, $eventType) {
                    /**
                     * @var Participation $participant
                     */
                    foreach ($participants as $participant) {
                        $this->notify($participant->messageable, $conversationId, $eventType, $messageId);
                    }
                }
            );
    }
}
