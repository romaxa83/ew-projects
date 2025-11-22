<?php

namespace App\GraphQL\Mutations\FrontOffice\Orders;

use App\Dto\Orders\OrderDto;
use App\Enums\Tickets\TicketStatusEnum;
use App\GraphQL\InputTypes\Orders\OrderInput;
use App\GraphQL\Types\NonNullType;
use App\GraphQL\Types\Orders\OrderType;
use App\Models\Catalog\Tickets\Ticket;
use App\Models\Orders\Order;
use App\Models\Technicians\Technician;
use App\Permissions\Orders\OrderCreatePermission;
use App\Services\Orders\OrderService;
use Closure;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class OrderCreateByTicketMutation extends BaseMutation
{
    public const NAME = 'orderCreateByTicket';
    public const PERMISSION = OrderCreatePermission::KEY;

    public function __construct(protected OrderService $orderService)
    {
        $this->setTechnicianGuard();
    }

    public function authorize(
        mixed $root,
        array $args,
        mixed $ctx,
        ResolveInfo $info = null,
        Closure $fields = null
    ): bool {
        return $this->can(static::PERMISSION) && $this->can('isActive', Technician::class);
    }

    /**
     * @return Type
     */
    public function type(): Type
    {
        return OrderType::nonNullType();
    }

    public function args(): array
    {
        return array_merge(
            [
                'ticket_id' => [
                    'type' => NonNullType::id(),
                    'rules' => [
                        'required',
                        'integer',
                        Rule::exists(Ticket::class, 'id')
                            ->whereIn('status', TicketStatusEnum::getOrderableValues()),
                    ]
                ],
            ],
            [
                'input' => OrderInput::nonNullType()
            ]
        );
    }

    /**
     * @param mixed $root
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @param SelectFields $fields
     * @return Order
     * @throws Throwable
     */
    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): Order
    {
        return makeTransaction(
            fn() => $this->orderService->createByTicket(
                Ticket::query()->findOrFail($args['ticket_id']),
                OrderDto::byArgs($args['input']),
                $this->user()
            )
        );
    }
}
