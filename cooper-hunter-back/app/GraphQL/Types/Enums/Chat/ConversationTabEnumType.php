<?php

namespace App\GraphQL\Types\Enums\Chat;

use App\Enums\Chat\ConversationTabEnum;
use App\GraphQL\Types\GenericBaseEnumType;

class ConversationTabEnumType extends GenericBaseEnumType
{
    public const NAME = 'ConversationTabEnumType';
    public const ENUM_CLASS = ConversationTabEnum::class;
}
