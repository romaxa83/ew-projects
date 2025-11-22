<?php

namespace App\GraphQL\Mutations\FrontOffice\Tickets;

use App\Dto\Catalog\Tickets\TicketByTechnicianDto;
use App\Enums\Tickets\TicketStatusEnum;
use App\GraphQL\InputTypes\Catalog\Tickets\TicketTranslationInput;
use App\GraphQL\Types\Catalog\Tickets\TicketType;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Tickets\Ticket;
use App\Permissions\Catalog\Tickets\UpdatePermission;
use App\Services\Catalog\Tickets\TicketService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class TicketUpdateMutation extends BaseMutation
{
    public const NAME = 'ticketUpdate';
    public const PERMISSION = UpdatePermission::KEY;

    public function __construct(private TicketService $service)
    {
        $this->setTechnicianGuard();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'rules' => [
                    'required',
                    'int',
                    Rule::exists(Ticket::class, 'id')
                        ->where('status', TicketStatusEnum::NEW)
                ],
            ],
            'translations' => [
                'type' => TicketTranslationInput::nonNullList(),
                'description' => 'Should be in English',
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
            fn() => $this->service->updateByTechnician(
                Ticket::find($args['id']),
                TicketByTechnicianDto::byArgs($args)
            )
        );
    }
}