<?php

namespace App\GraphQL\Types\Companies;

use App\GraphQL\Types\Localization\LanguageType;
use App\GraphQL\Types\NonNullType;
use App\GraphQL\Types\Users\UserType;
use App\Models\Companies\Company;
use GraphQL\Type\Definition\Type;

class CompanyType extends PublicCompanyInfoType
{
    public const NAME = 'CompanyType';

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'name' => [
                    'type' => Type::string(),
                    'resolve' => fn(Company $c) => $c->getName(),
                ],
                'language' => [
                    'type' => LanguageType::type()
                ],
                'email' => [
                    'type' => NonNullType::string(),
                    'selectable' => false,
                    'is_relation' => false,
                    'resolve' => fn(Company $c) => $c->owner->email
                ],
                'users' => [
                    'type' => Type::listOf(UserType::type()),
                ],
                'status' => [
                    'type' => NonNullType::string(),
                    'selectable' => false,
                    'is_relation' => false,
                ],
            ]
        );
    }
}
