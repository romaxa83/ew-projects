<?php

namespace App\GraphQL\InputTypes\Orders\BackOffice;

use App\GraphQL\InputTypes\Orders\OrderShippingInput;
use GraphQL\Type\Definition\Type;

class OrderShippingBackOfficeInput extends OrderShippingInput
{
    public const NAME = 'OrderShippingBackOfficeInput';

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'trk_number' => [
                    'type' => Type::string()
                ]
            ]
        );
    }
}
