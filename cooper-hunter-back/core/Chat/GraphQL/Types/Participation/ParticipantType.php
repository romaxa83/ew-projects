<?php

namespace Core\Chat\GraphQL\Types\Participation;

use Core\Chat\Contracts\Messageable;
use Core\GraphQL\Types\BaseType;
use GraphQL\Type\Definition\Type;

class ParticipantType extends BaseType
{
    public const NAME = 'ParticipantType';

    public function fields(): array
    {
        return [
            'name' => [
                'type' => Type::nonNull(Type::string()),
                'resolve' => static fn(Messageable $m): string => $m->getName(),
            ],
            'email' => [
                'type' => Type::string(),
            ],
            'phone' => [
                'type' => Type::string(),
            ],
        ];
    }
}
