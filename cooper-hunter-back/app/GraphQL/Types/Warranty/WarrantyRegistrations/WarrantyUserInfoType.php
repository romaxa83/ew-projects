<?php

namespace App\GraphQL\Types\Warranty\WarrantyRegistrations;

use App\Entities\Warranty\WarrantyUserInfo;
use App\Enums\Users\UserMorphEnum;
use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Enums\Users\UserMorphTypeEnum;
use App\GraphQL\Types\NonNullType;
use GraphQL\Type\Definition\Type;

class WarrantyUserInfoType extends BaseType
{
    public const NAME = 'WarrantyUserInfoType';

    public function fields(): array
    {
        return [
            'member_type' => [
                'type' => UserMorphTypeEnum::nonNullType(),
                'resolve' => static fn(WarrantyUserInfo $i) => $i->is_user
                    ? UserMorphEnum::USER
                    : UserMorphEnum::TECHNICIAN,
            ],
            'first_name' => [
                'type' => NonNullType::string(),
            ],
            'last_name' => [
                'type' => NonNullType::string(),
            ],
            'email' => [
                'type' => NonNullType::string(),
            ],
            'company_name' => [
                'type' => Type::string(),
            ],
            'company_address' => [
                'type' => Type::string(),
            ],
        ];
    }
}
