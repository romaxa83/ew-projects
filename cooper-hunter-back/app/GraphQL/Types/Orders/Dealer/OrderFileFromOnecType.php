<?php

namespace App\GraphQL\Types\Orders\Dealer;

use App\GraphQL\Types\BaseType;
use GraphQL\Type\Definition\Type;

class OrderFileFromOnecType extends BaseType
{
    public const NAME = 'dealerOrderFileFromOnec';

    public function fields(): array
    {
        return [
            'name' => [
                'type' => Type::string(),
            ],
            'url' => [
                'type' => Type::string(),
            ],
        ];
    }
}
