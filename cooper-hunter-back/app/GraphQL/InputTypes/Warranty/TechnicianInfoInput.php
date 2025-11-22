<?php

namespace App\GraphQL\InputTypes\Warranty;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\NonNullType;
use App\Rules\NameRule;

class TechnicianInfoInput extends BaseInputType
{
    public const NAME = 'WarrantyTechnicianInfoInput';

    public function fields(): array
    {
        return [
            'first_name' => [
                'type' => NonNullType::string(),
                'rules' => [
                    'string',
//                    new NameRule()
                ],
            ],
            'last_name' => [
                'type' => NonNullType::string(),
                'rules' => [
                    'string',
//                    new NameRule()
                ],
            ],
            'email' => [
                'type' => NonNullType::string(),
                'rules' => ['string', 'email:filter'],
            ],
            'company_name' => [
                'type' => NonNullType::string(),
            ],
            'company_address' => [
                'type' => NonNullType::string(),
            ],
        ];
    }
}
