<?php

namespace App\GraphQL\Mutations\BackOffice\Orders\Deliveries;

use App\Dto\Orders\OrderDeliveryTypeDto;
use App\GraphQL\InputTypes\SimpleTranslationWithDescriptionInput;
use App\GraphQL\Types\Orders\Deliveries\OrderDeliveryTypeType;
use App\Models\Orders\Deliveries\OrderDeliveryType;
use App\Permissions\Orders\DeliveryTypes\OrderDeliveryTypeCreatePermission;
use App\Rules\TranslationsArrayValidator;
use App\Services\Orders\OrderDeliveryTypeService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class OrderDeliveryTypeCreateMutation extends BaseMutation
{
    public const NAME = 'orderDeliveryTypeCreate';
    public const PERMISSION = OrderDeliveryTypeCreatePermission::KEY;

    public function __construct(protected OrderDeliveryTypeService $deliveryTypeService)
    {
        $this->setAdminGuard();
    }

    /**
     * @return Type
     */
    public function type(): Type
    {
        return OrderDeliveryTypeType::nonNullType();
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
     * @return OrderDeliveryType
     * @throws Throwable
     */
    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): OrderDeliveryType
    {
        return makeTransaction(
            fn() => $this->deliveryTypeService->create(
                OrderDeliveryTypeDto::byArgs($args)
            )
        );
    }
}
