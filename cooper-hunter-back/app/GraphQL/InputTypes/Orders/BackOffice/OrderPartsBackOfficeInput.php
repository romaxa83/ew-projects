<?php

namespace App\GraphQL\InputTypes\Orders\BackOffice;

use App\GraphQL\InputTypes\Orders\OrderPartsInput;
use GraphQL\Type\Definition\Type;

class OrderPartsBackOfficeInput extends OrderPartsInput
{
    public const NAME = 'OrderPartsBackOfficeInput';

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'price' => [
                    'type' => Type::float()
                ]
            ]
        );
    }
}
