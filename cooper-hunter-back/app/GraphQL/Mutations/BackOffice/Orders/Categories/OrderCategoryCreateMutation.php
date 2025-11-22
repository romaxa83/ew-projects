<?php

namespace App\GraphQL\Mutations\BackOffice\Orders\Categories;

use App\Dto\Orders\OrderCategoryDto;
use App\GraphQL\InputTypes\SimpleTranslationWithDescriptionInput;
use App\GraphQL\Types\Orders\Categories\OrderCategoryType;
use App\Models\Orders\Categories\OrderCategory;
use App\Permissions\Orders\Categories\OrderCategoryCreatePermission;
use App\Rules\TranslationsArrayValidator;
use App\Services\Orders\OrderCategoryService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class OrderCategoryCreateMutation extends BaseMutation
{
    public const NAME = 'orderCategoryCreate';
    public const PERMISSION = OrderCategoryCreatePermission::KEY;

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
            'active' => [
                'type' => Type::boolean(),
                'rules' => [
                    'nullable',
                    'boolean'
                ],
            ],
            'translations' => [
                'type' => SimpleTranslationWithDescriptionInput::list(),
                'rules' => [
                    'required',
                    'array',
                    new TranslationsArrayValidator(),
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
            fn() => $this->orderCategoryService->create(
                OrderCategoryDto::byArgs($args)
            )
        );
    }
}
