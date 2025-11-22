<?php

namespace App\GraphQL\Types\Users;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Localization\LanguageType;
use App\GraphQL\Types\NonNullType;
use App\GraphQL\Types\Roles\PermissionType;
use App\Models\Users\User;
use GraphQL\Type\Definition\Type;
use Illuminate\Support\Collection;

class UserProfileType extends BaseType
{
    public const NAME = 'UserProfileType';
    public const MODEL = User::class;

    public function fields(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id()
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
            'is_verify_email' => [
                'type' => Type::boolean(),
                'is_relation' => false,
                'selectable' => false,
                'always' => 'email_verified_at',
                'resolve' => fn(User $model) =>  $model->isEmailVerified()
            ],
            'email_verified_at' => [
                'type' => Type::string(),
            ],
            'phone_verified_at' => [
                'type' => Type::string(),
            ],
            'lang' => [
                'type' => Type::string(),
            ],
            'permissions' => [
                /** @see UserProfileType::resolvePermissionsField() */
                'type' => Type::listOf(
                    PermissionType::type()
                ),
                'is_relation' => false,
            ],
            'language' => [
                'type' => LanguageType::type(),
                'is_relation' => true,
            ],
        ];
    }

    protected function resolvePermissionsField(User $root): Collection
    {
        return $root->getAllPermissions();
    }

    protected function resolveAlertsField(User $root): Collection
    {
        return $root->getAlerts();
    }
}
