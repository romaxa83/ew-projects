<?php

namespace Core\GraphQL\Queries;

use App\GraphQL\Types\NonNullType;
use Closure;
use Core\Traits\Auth\AuthGuardsTrait;
use Core\Traits\GraphQL\BaseAttributesTrait;
use Core\Traits\GraphQL\Queries\BetweenDateRangeTrait;
use Core\Traits\GraphQL\ThrowableResolverTrait;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Query;

abstract class BaseQuery extends Query
{
    use BaseAttributesTrait;
    use AuthGuardsTrait;
    use BetweenDateRangeTrait;
    use ThrowableResolverTrait;

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

    protected function buildArgs(?array $sort = null, ?array $query = null): array
    {
        $args = [
            'id' => [
                'name' => 'id',
                'type' => Type::id()
            ],
            'per_page' => [
                'type' => NonNullType::int(),
                'defaultValue' => config('queries.default.pagination.per_page')
            ],
            'page' => [
                'type' => NonNullType::int(),
                'defaultValue' => 1
            ],
        ];

        if ($sort) {
            $defaultValue = $sort['default_value'] ?? null;
            $sort = $sort['fields'] ?? $sort;

            $args['sort'] = [
                'type' => Type::listOf(
                    NonNullType::string()
                ),
                'description' => 'Sorting data. Available fields: ' . implode(
                        ', ',
                        $sort
                    ) . '. E.g. ' . $sort[0] . '-desc',
            ];

            if ($defaultValue !== null) {
                $args['sort']['defaultValue'] = $defaultValue;
            }
        }

        if ($query) {
            $args['query'] = [
                'type' => Type::string(),
                'description' => 'Filter by fields: ' . implode(', ', $query)
            ];
        }
        return $args;
    }

    public function args(): array
    {
        return $this->buildArgs();
    }

    abstract public function type(): Type;
}
