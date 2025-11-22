<?php

namespace App\GraphQL\InputTypes\Catalog\Tickets;

use App\GraphQL\InputTypes\BaseTranslationInput;
use App\GraphQL\Types\NonNullType;

class TicketTranslationInput extends BaseTranslationInput
{
    public const NAME = 'TicketTranslationInput';

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'title' => [
                    'type' => NonNullType::string(),
                ],
                'description' => [
                    'type' => NonNullType::string(),
                ],
            ],
        );
    }
}