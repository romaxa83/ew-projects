<?php

namespace Core\Chat\GraphQL\Types\Message;

use Core\GraphQL\Types\BaseType;
use GraphQL\Type\Definition\Type;

class MessageMetaType extends BaseType
{
    public const NAME = 'MessageMetaType';

    public function fields(): array
    {
        return [
            'url' => [
                'type' => Type::nonNull(Type::string()),
                'description' => 'The URL where the attachment is available',
            ],
            'name' => [
                'type' => Type::nonNull(Type::string()),
                'description' => 'Name of the attached file',
            ],
        ];
    }
}
