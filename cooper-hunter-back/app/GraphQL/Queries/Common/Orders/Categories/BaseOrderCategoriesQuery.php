<?php


namespace App\GraphQL\Queries\Common\Orders\Categories;


use App\GraphQL\Types\Orders\Categories\OrderCategoryType;
use App\Permissions\Orders\Categories\OrderCategoryListPermission;
use App\Services\Orders\OrderCategoryService;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Database\Eloquent\Collection;
use Rebing\GraphQL\Support\SelectFields;

abstract class BaseOrderCategoriesQuery extends BaseQuery
{

    public const NAME = 'orderCategories';
    public const PERMISSION = OrderCategoryListPermission::KEY;

    public function __construct(protected OrderCategoryService $orderCategoryService)
    {
        $this->setQueryGuard();
    }

    abstract protected function setQueryGuard(): void;

    /**
     * @return array[]
     */
    public function args(): array
    {
        return [
            'id' => [
                'type' => Type::id(),
            ],
            'query' => [
                'type' => Type::string(),
                'description' => 'Field to filter by "title" or "description" fields',
                'rules' => [
                    'nullable',
                    'string'
                ]
            ],
        ];
    }

    /**
     * @return Type
     */
    public function type(): Type
    {
        return OrderCategoryType::list();
    }

    /**
     * @param mixed $root
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @param SelectFields $fields
     * @return Collection
     */
    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): Collection
    {
        return $this->orderCategoryService->getList($args, $this->user());
    }
}
