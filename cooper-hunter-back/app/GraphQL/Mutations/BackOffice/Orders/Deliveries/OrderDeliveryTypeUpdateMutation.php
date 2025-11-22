<?php

namespace App\GraphQL\Mutations\BackOffice\Orders\Deliveries;

use App\Dto\Orders\OrderDeliveryTypeDto;
use App\GraphQL\InputTypes\SimpleTranslationWithDescriptionInput;
use App\GraphQL\Types\NonNullType;
use App\GraphQL\Types\Orders\Categories\OrderCategoryType;
use App\Models\Orders\Deliveries\OrderDeliveryType;
use App\Permissions\Orders\DeliveryTypes\OrderDeliveryTypeUpdatePermission;
use App\Rules\TranslationsArrayValidator;
use App\Services\Orders\OrderDeliveryTypeService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class OrderDeliveryTypeUpdateMutation extends BaseMutation
{
    public const NAME = 'orderDeliveryTypeUpdate';
    public const PERMISSION = OrderDeliveryTypeUpdatePermission::KEY;

    public function __construct(protected OrderDeliveryTypeService $deliveryTypeService)
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
                        OrderDeliveryType::class,
                        'id'
                    )
                ]
            ],
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
     * @return OrderDeliveryType
     * @throws Throwable
     */
    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): OrderDeliveryType
    {
        return makeTransaction(
            fn() => $this->deliveryTypeService->update(
                OrderDeliveryTypeDto::byArgs($args)
            )
        );
    }
}
