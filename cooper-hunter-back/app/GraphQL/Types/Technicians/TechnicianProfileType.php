<?php

namespace App\GraphQL\Types\Technicians;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Localization\LanguageType;
use App\GraphQL\Types\Locations\CountryType;
use App\GraphQL\Types\Locations\StateType;
use App\GraphQL\Types\Media\MediaType;
use App\GraphQL\Types\NonNullType;
use App\GraphQL\Types\Roles\PermissionType;
use App\Models\Technicians\Technician;
use GraphQL\Type\Definition\Type;
use Illuminate\Support\Collection;

class TechnicianProfileType extends BaseType
{
    public const NAME = 'TechnicianProfileType';
    public const MODEL = Technician::class;

    public function fields(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id()
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
            'phone' => [
                'type' => Type::string(),
            ],
            'email_verified_at' => [
                'type' => Type::string(),
            ],
            'is_verify_email' => [
                'type' => Type::boolean(),
                'is_relation' => false,
                'selectable' => false,
                'always' => 'email_verified_at',
                'resolve' => fn(Technician $model) =>  $model->isEmailVerified()
            ],
            'phone_verified_at' => [
                'type' => Type::string(),
            ],
            'lang' => [
                'type' => Type::string(),
            ],
            'permissions' => [
                /** @see TechnicianProfileType::resolvePermissionsField() */
                'type' => Type::listOf(
                    PermissionType::type()
                ),
                'is_relation' => false,
            ],
            'language' => [
                'type' => LanguageType::type(),
                'is_relation' => true,
            ],
            'state' => [
                'type' => StateType::type(),
                'always' => 'state_id',
            ],
            'country' => [
                'type' => CountryType::type(),
                'always' => 'country_id',
            ],
            'avatar' => [
                'type' => MediaType::type(),
                'is_relation' => false,
                'selectable' => false,
                'resolve' => static fn(Technician $a) => $a->avatar()
            ]
        ];
    }

    protected function resolvePermissionsField(Technician $root): Collection
    {
        return $root->getAllPermissions();
    }
}
