<?php

namespace App\GraphQL\Types\Technicians;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Locations\CountryType;
use App\GraphQL\Types\Locations\StateType;
use App\GraphQL\Types\NonNullType;
use App\GraphQL\Types\Roles\RoleType;
use App\Models\Technicians\Technician;
use Core\GraphQL\Fields\PermissionField;
use GraphQL\Type\Definition\Type;

class TechnicianType extends BaseType
{
    public const NAME = 'technician';
    public const MODEL = Technician::class;

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'state' => [
                    'type' => StateType::nonNullType(),
                    'always' => 'id',
                ],
                'country' => [
                    'type' => CountryType::nonNullType(),
                ],
                'is_certified' => [
                    'type' => NonNullType::boolean(),
                ],
                'is_verified' => [
                    'type' => NonNullType::boolean(),
                ],
                'is_commercial_certification' => [
                    'type' => NonNullType::boolean(),
                ],
                'hvac_license' => [
                    'type' => Type::string(),
                ],
                'epa_license' => [
                    'type' => Type::string(),
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
                'lang' => [
                    'type' => Type::string(),
                ],
                'is_verify_email' => [
                    'type' => Type::boolean(),
                    'is_relation' => false,
                    'selectable' => false,
                    'always' => 'email_verified_at',
                    'resolve' => fn(Technician $model) =>  $model->isEmailVerified()
                ],
                'permission' => PermissionField::class,
                'roles' => [
                    'type' => Type::listOf(RoleType::type()),
                ]
            ]
        );
    }
}
