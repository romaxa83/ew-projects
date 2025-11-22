<?php

namespace App\GraphQL\Queries\FrontOffice\Catalog\Filters;

use App\GraphQL\Types\Catalog\Filters\FilterType;
use App\Models\Catalog\Categories\Category;
use App\Services\Filters\FilterService;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;

class FilterQuery extends BaseQuery
{
    public const NAME = 'filter';

    public function __construct(protected FilterService $service)
    {
    }

    public function args(): array
    {
        return [
            'category_id' => [
                'type' => Type::id(),
                'rules' => [Rule::exists(Category::class, 'id')],
                'description' => 'Get filters for category',
            ],
            'category_slug' => [
                'type' => Type::string(),
                'rules' => [Rule::exists(Category::class, 'slug')],
                'description' => 'Get filters for category',
            ],
            'search_query' => [
                'type' => Type::string(),
                'description' => 'Get filters for search page (when category not specified)'
            ],
        ];
    }

    public function type(): Type
    {
        return FilterType::nonNullList();
    }

    /**
     * @param mixed $root
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @param SelectFields $fields
     * @return Collection
     */
    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): Collection {
        return $this->service->getFiltersForSearch($args);
    }
}
