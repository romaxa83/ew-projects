<?php

namespace App\GraphQL\Queries\Common\About;

use App\GraphQL\Types\About\Pages\PageType;
use App\Services\About\PageService;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\Rules\Exists;
use Rebing\GraphQL\Support\SelectFields;

abstract class BasePageQuery extends BaseQuery
{
    public const NAME = 'page';

    public function __construct(private PageService $service)
    {
        $this->setQueryGuard();
    }

    abstract protected function setQueryGuard(): void;

    public function args(): array
    {
        return array_merge(
            [
                'id' => [
                    'type' => Type::id(),
                    'rules' => [
                        'nullable',
                        'int',
                        $this->idRuleExists()
                    ],
                    'description' => 'Filter by ID.',
                ],
                'query' => [
                    'type' => Type::string(),
                    'description' => 'Filter by page title.',
                ],
                'page' => [
                    'type' => Type::int(),
                    'defaultValue' => 1,
                ],
                'per_page' => [
                    'type' => Type::int(),
                    'defaultValue' => config('queries.default.pagination.per_page'),
                ]
            ],
            $this->getSlugArgs()
        );
    }

    abstract protected function idRuleExists(): Exists;

    public function type(): Type
    {
        return PageType::paginate();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): LengthAwarePaginator
    {
        return $this->service->getList($args);
    }
}
