<?php

namespace App\GraphQL\Types\Messages;

use App\Entities\Messages\ResponseMessageEntity;
use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Enums\Messages\MessageKindEnumType;
use App\GraphQL\Types\NonNullType;
use Core\Enums\Messages\MessageTypeEnum;

/**
 * @see ResponseMessageEntity
 */
class ResponseMessageType extends BaseType
{
    public const NAME = 'ResponseMessageType';
    public const DESCRIPTION = 'Сообщение для отображения на фронте.';

    public function fields(): array
    {
        return [
            'message' => [
                'type' => NonNullType::string(),
            ],
            'type' => [
                'type' => MessageKindEnumType::nonNullType(),
                'description' => 'Возможные варианты: ' . MessageTypeEnum::listToString(),
            ],
        ];
    }
}
