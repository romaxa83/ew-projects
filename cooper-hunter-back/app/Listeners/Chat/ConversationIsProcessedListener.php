<?php

namespace App\Listeners\Chat;

use App\Events\Chat\ConversationIsProcessed;
use App\Models\Admins\Admin;
use Core\Chat\Contracts\Messageable;
use Core\Chat\Enums\ConversationUpdatedEventTypeEnum;
use Core\Chat\Models\Conversation;
use Core\Chat\Models\Participation;

class ConversationIsProcessedListener extends BaseChatEventListener
{
    public function handle(ConversationIsProcessed $event): void
    {
        $this->notifyAdmins(
            $event->getConversation(),
            $event->getProcessedBy(),
        );

        $this->notifyUsers($event->getConversation());
    }

    protected function notifyAdmins(Conversation $conversation, ?Messageable $exceptMe = null): void
    {
        foreach (Admin::getChatAdmins() as $admin) {
            if ($exceptMe && $admin->getKey() === $exceptMe->getKey()) {
                continue;
            }

            $this->notify(
                $admin,
                $conversation->id,
                ConversationUpdatedEventTypeEnum::CONVERSATION_PROCESSED()
            );
        }
    }

    protected function notifyUsers(Conversation $conversation): void
    {
        $participants = $conversation->participants()
            ->where('messageable_type', '<>', Admin::MORPH_NAME)
            ->cursor();

        /**
         * @var Participation $participant
         */
        foreach ($participants as $participant) {
            $this->notify(
                $participant->messageable,
                $conversation->id,
                ConversationUpdatedEventTypeEnum::CONVERSATION_PROCESSED()
            );
        }
    }
}
