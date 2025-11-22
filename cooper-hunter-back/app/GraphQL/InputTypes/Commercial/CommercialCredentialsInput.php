<?php

namespace App\GraphQL\InputTypes\Commercial;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\NonNullType;
use App\Rules\PhoneRule;
use GraphQL\Type\Definition\Type;

class CommercialCredentialsInput extends BaseInputType
{
    public const NAME = 'CommercialCredentialsInput';

    public function fields(): array
    {
        return [
            'company_name' => [
                'type' => NonNullType::string(),
            ],
            'company_phone' => [
                'type' => NonNullType::string(),
                'rules' => [new PhoneRule()],
            ],
            'company_email' => [
                'type' => NonNullType::string(),
                'rules' => ['email:filter'],
            ],
            'project_id' => [
                'type' => NonNullType::id(),
            ],
            'comment' => [
                'type' => Type::string(),
                'rules' => ['nullable', 'string', 'max:1000'],
            ],
        ];
    }
}