<?php

namespace App\GraphQL\Types\Warranty\WarrantyRegistrations;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Enums\Projects\Systems\WarrantyStatusEnumType;
use App\GraphQL\Types\Enums\Warranties\WarrantyTypeEnumType;
use App\GraphQL\Types\Projects\ProjectSystemType;
use App\GraphQL\Types\Projects\ProjectSystemUnitType;
use App\GraphQL\Types\Users\UserMorphType;
use App\Models\Warranty\WarrantyRegistration;
use GraphQL\Type\Definition\Type;

class WarrantyRegistrationType extends BaseType
{
    public const NAME = 'WarrantyRegistrationType';
    public const MODEL = WarrantyRegistration::class;

    public function fields(): array
    {
        $fields = [
            'notice' => [
                'type' => Type::string(),
                'description' => 'Some notice for the warranty. Usually indicates to "void" or "denied" reason'
            ],
            'warranty_status' => [
                'type' => WarrantyStatusEnumType::nonNullType(),
            ],
            'type' => [
                'type' => WarrantyTypeEnumType::nonNullType(),
            ],
            'member' => [
                'type' => UserMorphType::type(),
                'description' => 'Type of authorized user applying for registration, if any.',
            ],
            'user_info' => [
                'type' => WarrantyUserInfoType::nonNullType(),
                'selectable' => false,
                'is_relation' => false,
                'always' => 'user_info',
            ],
            'system' => [
                'type' => ProjectSystemType::type(),
                'description' => 'System to which the registration belongs, if any.',
            ],
            'units' => [
                'type' => ProjectSystemUnitType::nonNullList(),
            ],
        ];

        return array_merge(
            parent::fields(),
            $fields,
        );
    }
}
