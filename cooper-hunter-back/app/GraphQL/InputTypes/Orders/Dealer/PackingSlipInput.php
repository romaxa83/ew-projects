<?php

namespace App\GraphQL\InputTypes\Orders\Dealer;

use App\GraphQL\Types\BaseInputType;
use GraphQL\Type\Definition\Type;

class PackingSlipInput extends BaseInputType
{
    public const NAME = 'DealerOrderPackingSlipInput';

    public function fields(): array
    {
        return [
            'tracking_number' => [
                'type' => Type::string(),
            ],
            'tracking_company' => [
                'type' => Type::string(),
            ],
        ];
    }
}

