<?php

namespace App\GraphQL\Queries\FrontOffice\Catalog\Search;

use App\Dto\Catalog\SearchDto;
use App\GraphQL\Types\Catalog\Search\SearchUnionType;
use App\GraphQL\Types\NonNullType;
use App\Services\Catalog\SearchService;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Support\Collection;
use JetBrains\PhpStorm\ArrayShape;
use Rebing\GraphQL\Support\SelectFields;

class SearchUnionQuery extends BaseQuery
{
    public const NAME = 'searchUnion';

    public function __construct(private SearchService $searchService)
    {
    }

    public function type(): Type
    {
        return SearchUnionType::list();
    }

    #[ArrayShape(['query' => "array"])]
    public function args(): array
    {
        return [
            'query' => [
                'type' => NonNullType::string(),
                'description' => 'Search by category/product name'
            ]
        ];
    }

    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): Collection
    {
        return $this->searchService->search(
            SearchDto::byArgs($args)
        );
    }

    #[ArrayShape([
        'query' => "string[]"
    ])]
    public function rules(array $args = []): array
    {
        return [
            'query' => [
                'required',
                'string',
                "min:2"
            ]
        ];
    }
}
