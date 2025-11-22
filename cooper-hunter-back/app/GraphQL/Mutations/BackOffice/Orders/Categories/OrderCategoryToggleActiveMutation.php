<?php

namespace App\GraphQL\Mutations\BackOffice\Orders\Categories;

use App\Dto\Orders\OrderCategoryDto;
use App\GraphQL\Types\NonNullType;
use App\GraphQL\Types\Orders\Categories\OrderCategoryType;
use App\Models\Orders\Categories\OrderCategory;
use App\Permissions\Orders\Categories\OrderCategoryUpdatePermission;
use App\Services\Orders\OrderCategoryService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class OrderCategoryToggleActiveMutation extends BaseMutation
{
    public const NAME = 'orderCategoryToggleActive';
    public const PERMISSION = OrderCategoryUpdatePermission::KEY;

    public function __construct(protected OrderCategoryService $orderCategoryService)
    {
        $this->setAdminGuard();
    }

    /**
     * @return Type
     */
    public function type(): Type
    {
        return OrderCategoryType::type();
    }

    /**
     * @return array[]
     */
    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'rules' => [
                    'required',
                    'integer',
                    Rule::exists(
                        OrderCategory::class,
                        'id'
                    )
                ]
            ],
        ];
    }

    /**
     * @param mixed $root
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @param SelectFields $fields
     * @return OrderCategory
     * @throws Throwable
     */
    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): OrderCategory
    {
        return makeTransaction(
            fn() => $this->orderCategoryService->toggleActive(
                OrderCategoryDto::byArgs($args)
            )
        );
    }
}
