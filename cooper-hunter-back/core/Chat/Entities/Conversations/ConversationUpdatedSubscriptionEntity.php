<?php

namespace Core\Chat\Entities\Conversations;

use Core\Chat\Enums\ConversationUpdatedEventTypeEnum;

class ConversationUpdatedSubscriptionEntity
{
    public ConversationUpdatedEventTypeEnum $event;
    public int $conversation_id;
    public ?int $message_id;

    public static function makeByContext(array $context): self
    {
        $self = new self();

        $self->event = ConversationUpdatedEventTypeEnum::fromValue($context['event']);
        $self->conversation_id = $context['conversation_id'];
        $self->message_id = $context['message_id'] ?? null;

        return $self;
    }
}
