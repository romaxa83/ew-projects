<?php

namespace App\GraphQL\Mutations\FrontOffice\Users;

use App\GraphQL\Types\Users\UserType;
use App\Models\Users\User;
use App\Services\Users\UserService;
use Closure;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;

class UserAvatarDeleteMutation extends BaseMutation
{
    public const NAME = 'userAvatarDelete';

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
        return [];
    }

    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): User
    {
        $user = $this->getAuthGuard()->user();
        $this->service->deleteAvatar($user);

        return $user->refresh();
    }
}
