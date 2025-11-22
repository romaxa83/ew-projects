<?php

namespace App\GraphQL\Types\SupportRequests;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;
use App\GraphQL\Types\Users\UserMorphType;
use App\Models\Support\SupportRequestMessage;

class SupportRequestMessageType extends BaseType
{
    public const NAME = 'SupportRequestMessageType';
    public const MODEL = SupportRequestMessage::class;

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'text' => [
                    'type' => NonNullType::string(),
                    'alias' => 'message',
                ],
                'sender' => [
                    'type' => UserMorphType::nonNullType(),
                    'is_relation' => true,
                ],
                'is_read' => [
                    'type' => NonNullType::boolean(),
                    'resolve' => fn(SupportRequestMessage $message) => !empty($message->is_read)
                ]
            ]
        );
    }


}
