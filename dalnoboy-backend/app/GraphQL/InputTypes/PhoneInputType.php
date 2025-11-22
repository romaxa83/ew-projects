<?php


namespace App\GraphQL\InputTypes;


use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\NonNullType;

class PhoneInputType extends BaseInputType
{
    public const NAME = 'PhoneInputType';

    public function fields(): array
    {
        return [
            'phone' => [
                'type' => NonNullType::string(),
                'description' => 'Phone for regex: /^380[1-9][0-9]{8}$/',
                'rules' => [
                    'required',
                    'string',
                    'regex:/^[0-9]{3,}$/',
                ],
            ],
            'is_default' => [
                'type' => NonNullType::boolean(),
                'description' => 'Flag for default phone. Only one phone has to have this flag',
                'defaultValue' => false
            ]
        ];
    }
}
