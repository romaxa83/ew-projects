<?php

namespace App\GraphQL\Mutations\FrontOffice\Users;

use App\Services\Users\UserService;
use Closure;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;

class UserDeleteProfileMutation extends BaseMutation
{
    public const NAME = 'userDeleteProfile';

    public function __construct(protected UserService $service)
    {
        $this->setUserGuard();
    }

    public function authorize(
        mixed $root,
        array $args,
        mixed $ctx,
        ResolveInfo $info = null,
        Closure $fields = null
    ): bool {
        return $this->authCheck();
    }

    public function type(): Type
    {
        return Type::boolean();
    }

    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): bool
    {
        return $this->service->deleteProfile($this->user());
    }
}
