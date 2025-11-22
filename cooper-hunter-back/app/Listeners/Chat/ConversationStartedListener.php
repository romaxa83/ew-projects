<?php

namespace App\Listeners\Chat;

use App\Models\Admins\Admin;
use Core\Chat\Enums\ConversationUpdatedEventTypeEnum;
use Core\Chat\Events\ConversationStarted;
use Core\Chat\Models\Conversation;

class ConversationStartedListener extends BaseChatEventListener
{
    public function handle(ConversationStarted $event): void
    {
        $conversation = $event->getConversation();

        $this->notifyAdmins($conversation);
    }

    protected function notifyAdmins(Conversation $conversation): void
    {
        $eventType = ConversationUpdatedEventTypeEnum::CONVERSATION_STARTED();

        foreach (Admin::getChatAdmins() as $admin) {
            $this->notify($admin, $conversation->id, $eventType);
        }
    }
}
