<?php

namespace Core\GraphQL\Queries;

use App\Traits\GraphQL\ActiveHelperTrait;
use App\Traits\GraphQL\IdsHelperTrait;
use App\Traits\GraphQL\QueryTextHelperTrait;
use App\Traits\GraphQL\SlugsHelperTrait;
use Closure;
use Core\Traits\Auth\AuthGuardsTrait;
use Core\Traits\Auth\TechnicianCommercial;
use Core\Traits\GraphQL\BaseAttributesTrait;
use Core\Traits\GraphQL\Queries\BetweenDateRangeTrait;
use Core\Traits\GraphQL\Queries\PaginateHelperTrait;
use Core\Traits\GraphQL\Queries\SortHelperTrait;
use Core\Traits\GraphQL\RuleHelperTrait;
use Core\Traits\GraphQL\ThrowableResolverTrait;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Query;

abstract class BaseQuery extends Query
{
    use ActiveHelperTrait;
    use BaseAttributesTrait;
    use AuthGuardsTrait;
    use IdsHelperTrait;
    use SlugsHelperTrait;
    use QueryTextHelperTrait;
    use PaginateHelperTrait;
    use SortHelperTrait;
    use BetweenDateRangeTrait;
    use ThrowableResolverTrait;
    use RuleHelperTrait;
    use TechnicianCommercial;

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
            'id' => [
                'name' => 'id',
                'type' => Type::id()
            ],
            'created_at' => [
                'name' => 'created_at',
                'type' => Type::string()
            ],
            'updated_at' => [
                'name' => 'updated_at',
                'type' => Type::string()
            ],
            'per_page' => [
                'type' => Type::int(),
                'defaultValue' => config('queries.default.pagination.per_page')
            ],
            'page' => [
                'type' => Type::int(),
                'defaultValue' => 1
            ],
        ];
    }

    abstract public function type(): Type;
}
