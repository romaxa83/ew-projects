<?php

namespace Core\GraphQL\Queries;

use Closure;
use Core\Traits\Auth\AuthGuardsTrait;
use Core\Traits\GraphQL\BaseAttributesTrait;
use Core\Traits\GraphQL\Queries\BetweenDateRangeTrait;
use Core\Traits\GraphQL\Queries\PaginateHelperTrait;
use Core\Traits\GraphQL\Queries\SortHelperTrait;
use Core\Traits\GraphQL\ThrowableResolverTrait;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Query;

abstract class BaseQuery extends Query
{
    use BaseAttributesTrait;
    use AuthGuardsTrait;
    use PaginateHelperTrait;
    use SortHelperTrait;
    use BetweenDateRangeTrait;
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

    public function args(): array
    {
        return [
            'id' => ['name' => 'id', 'type' => Type::id()],
            'created_at' => ['name' => 'created_at', 'type' => Type::string()],
            'updated_at' => ['name' => 'updated_at', 'type' => Type::string()],
            'per_page' => ['type' => Type::int()],
            'page' => ['type' => Type::int()],
        ];
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
