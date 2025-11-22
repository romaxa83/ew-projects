<?php

namespace App\GraphQL\Mutations\FrontOffice\Orders;

use App\GraphQL\Types\Enums\Payments\PaymentReturnPlatformTypeEnum;
use App\GraphQL\Types\NonNullType;
use App\GraphQL\Types\Orders\OrderPaymentCheckoutUrlType;
use App\Models\Orders\Order;
use App\Permissions\Orders\OrderPaidPermission;
use App\Services\Orders\OrderService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Support\Collection;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class OrderPaymentCheckoutCreateMutation extends BaseMutation
{
    public const NAME = 'orderPaymentCheckoutCreate';
    public const PERMISSION = OrderPaidPermission::KEY;

    public function __construct(protected OrderService $orderService)
    {
        $this->setTechnicianGuard();
    }

    /**
     * @return Type
     */
    public function type(): Type
    {
        return OrderPaymentCheckoutUrlType::nonNullType();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'rules' => [
                    $this->existNoDeleted(Order::class)
                ]
            ],
            'platform' => [
                'type' => PaymentReturnPlatformTypeEnum::nonNullType()
            ]
        ];
    }

    /**
     * @param mixed $root
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @param SelectFields $fields
     * @return Collection
     * @throws Throwable
     */
    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): Collection {
        return makeTransaction(
            fn() => $this->orderService->payForOrder(
                $args['id'],
                $args['platform'],
                $this->user()
            )
        );
    }
}
