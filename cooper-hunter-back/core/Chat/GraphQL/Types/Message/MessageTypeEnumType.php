<?php

namespace Core\Chat\GraphQL\Types\Message;

use Core\Chat\Enums\MessageTypeEnum;
use Core\GraphQL\Types\GenericEnumType;

class MessageTypeEnumType extends GenericEnumType
{
    public const NAME = 'MessageTypeEnumType';
    public const ENUM_CLASS = MessageTypeEnum::class;
}
