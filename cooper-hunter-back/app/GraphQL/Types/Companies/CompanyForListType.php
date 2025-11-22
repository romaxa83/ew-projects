<?php

namespace App\GraphQL\Types\Companies;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;
use App\Models\Companies\Company;
use GraphQL\Type\Definition\Type;

class CompanyForListType extends BaseType
{
    public const NAME = 'companyForListType';
    public const MODEL = Company::class;

    public function fields(): array
    {
        return [
            'id' => [
                'type' => Type::nonNull(Type::id()),
            ],
            'business_name' => [
                'type' => NonNullType::string(),
            ],
        ];
    }
}

