<?php

namespace App\GraphQL\Queries\FrontOffice\Catalog\Manuals;

use App\GraphQL\Types\Catalog\Manuals\Categories\ManualCategoryProductGroupType;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Categories\Category;
use App\Services\Catalog\Manuals\ManualCategoryService;
use Core\GraphQL\Queries\BaseQuery;
use Exception;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;

class ManualCategoryQuery extends BaseQuery
{
    public const NAME = 'manualCategory';

    public function __construct(protected ManualCategoryService $service)
    {
    }

    public function args(): array
    {
        return [
            'category_id' => [
                'type' => NonNullType::id(),
                'rules' => [Rule::exists(Category::class, 'id')],
            ],
            'search' => [
                'type' => Type::string(),
                'description' => 'Search product by model (by name)',
                'rules' => ['nullable', 'string', 'min:2'],
            ],
        ];
    }

    public function type(): Type
    {
        return ManualCategoryProductGroupType::nonNullList();
    }

    /**
     * @throws Exception
     */
    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): Collection {
        return $this->service->getProductsForCategory($args['category_id'], $args['search'] ?? null);
    }
}
