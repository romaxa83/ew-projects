<?php

namespace Core\GraphQL\Mutations;

use Closure;
use Core\Services\Auth\AuthPassportService;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;

abstract class BaseLogoutMutation extends BaseMutation
{
    public function authorize(
        mixed $root,
        array $args,
        mixed $ctx,
        ResolveInfo $info = null,
        Closure $fields = null
    ): bool
    {
        return $this->getAuthGuard()->check();
    }

    public function type(): Type
    {
        return Type::boolean();
    }

    abstract protected function getPassportService(): AuthPassportService;

    public function doResolve($root, $args, $context, ResolveInfo $info, SelectFields $fields): bool
    {
        return $this->getPassportService()->logout(
            $this->user()
        );
    }
}
