<?php

namespace App\Listeners\Chat;

use App\Contracts\Members\Member;
use App\GraphQL\Subscriptions\BackOffice\Chat\ConversationUpdatedSubscription as B;
use App\GraphQL\Subscriptions\FrontOffice\Chat\ConversationUpdatedSubscription as F;
use App\Models\Admins\Admin;
use Core\Chat\Contracts\Messageable;
use Core\Chat\Enums\ConversationUpdatedEventTypeEnum;
use Core\WebSocket\Contracts\Subscribable;
use Illuminate\Contracts\Queue\ShouldQueue;

class BaseChatEventListener implements ShouldQueue
{
    protected function notify(
        Messageable $messageable,
        int $conversationId,
        ConversationUpdatedEventTypeEnum $eventType,
        ?int $messageId = null
    ): void {
        if (!$messageable instanceof Subscribable) {
            return;
        }

        if ($messageable instanceof Member) {
            $notifyBy = F::notify();
        }

        if ($messageable instanceof Admin) {
            $notifyBy = B::notify();
        }

        if (!isset($notifyBy)) {
            return;
        }

        $notifyBy->toUser($messageable)
            ->withContext(
                [
                    'event' => $eventType->value,
                    'conversation_id' => $conversationId,
                    'message_id' => $messageId,
                ]
            )
            ->broadcast();
    }
}
