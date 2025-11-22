<?php

namespace Core\Chat\GraphQL\Types\Conversation;

use Core\Chat\Enums\ConversationUpdatedEventTypeEnum;
use Core\GraphQL\Types\GenericEnumType;

class ConversationUpdatedEventTypeEnumType extends GenericEnumType
{
    public const NAME = 'ConversationUpdatedEventTypeEnumType';
    public const ENUM_CLASS = ConversationUpdatedEventTypeEnum::class;
}
