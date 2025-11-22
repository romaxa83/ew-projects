<?php

namespace App\GraphQL\Mutations\FrontOffice\Tickets;

use App\Dto\Catalog\Tickets\TicketByTechnicianDto;
use App\GraphQL\InputTypes\Catalog\Tickets\TicketInput;
use App\GraphQL\Types\Catalog\Tickets\TicketType;
use App\Models\Catalog\Tickets\Ticket;
use App\Permissions\Catalog\Tickets\CreatePermission;
use App\Rules\Orders\OrderPartRule;
use App\Services\Catalog\Tickets\TicketService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class TicketCreateMutation extends BaseMutation
{
    public const NAME = 'ticketCreate';
    public const PERMISSION = CreatePermission::KEY;

    public function __construct(private TicketService $service)
    {
        $this->setTechnicianGuard();
    }

    public function args(): array
    {
        return [
            'input' => [
                'type' => TicketInput::nonNullType(),
            ],
        ];
    }

    public function type(): Type
    {
        return TicketType::type();
    }

    /**
     * @throws Throwable
     */
    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): Ticket
    {
        return makeTransaction(
            fn() => $this->service->createByTechnician(
                $this->user(),
                TicketByTechnicianDto::byArgs($args['input'])
            )
        );
    }

    protected function rules(array $args = []): array
    {
        return [
            'input.order_parts.*' => [
                'nullable',
                'array',
                new OrderPartRule(),
            ],
        ];
    }
}