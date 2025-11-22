<?php

namespace App\GraphQL\Mutations\FrontOffice\Users;

use App\Dto\Users\UserSettingsDto;
use App\GraphQL\InputTypes\Users\UserSettingsInputType;
use App\GraphQL\Types\Users\UserType;
use App\Models\Users\User;
use App\Services\Users\UserService;
use Closure;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;

class UserSettingsUpdateMutation extends BaseMutation
{
    public const NAME = 'userSettingsUpdate';

    public function __construct(protected UserService $service)
    {
    }

    public function authorize($root, array $args, $ctx, ResolveInfo $info = null, Closure $fields = null): bool
    {
        return $this->getAuthGuard()->check();
    }

    public function type(): Type
    {
        return UserType::nonNullType();
    }

    public function args(): array
    {
        return [
            'settings' => [
                'type' => UserSettingsInputType::nonNullType()
            ]
        ];
    }

    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): User
    {
        return $this->service->updateSettings(
            UserSettingsDto::byArgs($args['settings']),
            $this->getAuthGuard()->user()
        );
    }
}
