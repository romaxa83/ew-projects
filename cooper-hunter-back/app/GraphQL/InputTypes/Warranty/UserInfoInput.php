<?php

namespace App\GraphQL\InputTypes\Warranty;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\NonNullType;
use App\Rules\NameRule;

class UserInfoInput extends BaseInputType
{
    public const NAME = 'WarrantyUserInfoInput';

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
        ];
    }
}
