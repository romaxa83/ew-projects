<?php

namespace Core\Chat\GraphQL\Types\Participation;

use Core\Chat\Models\Participation;
use Core\GraphQL\Types\BaseType;

class ParticipationType extends BaseType
{
    public const NAME = 'ParticipationType';
    public const MODEL = Participation::class;
    public const DESCRIPTION = 'Morph type for participants';

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'participant' => [
                    'type' => ParticipantType::nonNullType(),
                    'alias' => 'messageable',
                ],
            ]
        );
    }
}
