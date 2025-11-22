<?php

namespace App\GraphQL\InputTypes\Catalog\Tickets;

use App\GraphQL\InputTypes\Orders\OrderPartsInput;
use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\NonNullType;
use App\Rules\ExistsRules\SerialNumberExistsRule;
use GraphQL\Type\Definition\Type;

class TicketInput extends BaseInputType
{
    public const NAME = 'TicketInput';

    public function fields(): array
    {
        return [
            'serial_number' => [
                'type' => NonNullType::string(),
                'rules' => ['required', 'string', new SerialNumberExistsRule()],
            ],
            'order_parts' => [
                'type' => OrderPartsInput::list(),
                'rules' => [
                    'nullable',
                    'array',
                ]
            ],
            'comment' => [
                'type' => Type::string(),
            ],
            'translations' => [
                'type' => TicketTranslationInput::nonNullList(),
                'description' => 'Should be in English',
            ],
        ];
    }
}