<?php

namespace Core\GraphQL\Mutations;

use Closure;
use Core\Traits\Auth\AuthGuardsTrait;
use Core\Traits\GraphQL\BaseAttributesTrait;
use Core\Traits\GraphQL\ThrowableResolverTrait;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Mutation;

abstract class BaseMutation extends Mutation
{
    use AuthGuardsTrait;
    use BaseAttributesTrait;
    use ThrowableResolverTrait;

    protected array $middlewareMap = [];

    public const NAME = '';
    public const DESCRIPTION = '';
    public const PERMISSION = '';

    public function authorize(
        mixed $root,
        array $args,
        mixed $ctx,
        ResolveInfo $info = null,
        Closure $fields = null
    ): bool {

        return empty(static::PERMISSION) || $this->can(static::PERMISSION);
    }

    abstract public function type(): Type;

    protected function getMiddleware(): array
    {
        if (!empty($this->middleware)) {
            return $this->middleware;
        }

        $middlewares = [];

        foreach ($this->middlewareMap as $contractClassname => $middlewareClassname) {
            if ($this instanceof $contractClassname) {
                $middlewares[] = $middlewareClassname;
            }
        }

        return $middlewares;
    }
}
