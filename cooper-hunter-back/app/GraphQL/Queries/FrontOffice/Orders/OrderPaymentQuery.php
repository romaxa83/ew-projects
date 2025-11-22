<?php


namespace App\GraphQL\Queries\FrontOffice\Orders;


use App\GraphQL\Types\NonNullType;
use App\GraphQL\Types\Orders\OrderPaymentType;
use App\Models\Orders\Order;
use App\Models\Orders\OrderPayment;
use App\Models\Payments\PayPalCheckout;
use App\Permissions\Orders\OrderListPermission;
use App\Services\Orders\OrderService;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class OrderPaymentQuery extends BaseQuery
{
    public const NAME = 'orderPayment';
    public const PERMISSION = OrderListPermission::KEY;

    public function __construct(protected OrderService $orderService)
    {
        $this->setTechnicianGuard();
    }

    public function type(): Type
    {
        return OrderPaymentType::nonNullType();
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
                    $this->existNoDeleted(Order::class)
                ]
            ],
            'token_id' => [
                'type' => Type::id(),
                'description' => 'If need to update data of paypal payment session send this param (it is in the query params, when user returning from paypal).',
                'rules' => [
                    'nullable',
                    'string',
                    Rule::exists(PayPalCheckout::class, 'id')
                ]
            ]
        ];
    }

    /**
     * @param mixed $root
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @param SelectFields $fields
     * @return OrderPayment
     * @throws Throwable
     */
    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): OrderPayment {
        return makeTransaction(
            fn() => $this->orderService->checkPaid($args['id'], data_get($args, 'token_id'), $this->user())
        );
    }
}
