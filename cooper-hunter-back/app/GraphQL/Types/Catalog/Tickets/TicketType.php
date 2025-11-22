<?php

namespace App\GraphQL\Types\Catalog\Tickets;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Tickets\Ticket;
use App\Models\Technicians\Technician;
use App\Traits\GraphQL\HasGuidTrait;
use Core\Traits\Auth\AuthGuardsTrait;
use GraphQL\Type\Definition\Type;

class TicketType extends BaseType
{
    use HasGuidTrait;
    use AuthGuardsTrait;

    public const NAME = 'TicketType';
    public const MODEL = Ticket::class;

    public function fields(): array
    {
        return array_merge(
            $this->getGuidField(),
            [
                'id' => [
                    'type' => NonNullType::id()
                ],
                'code' => [
                    'type' => Type::string(),
                    'description' => 'An unique ticket code.'
                ],
                'order_parts' => [
                    'type' => NonNullType::listOfString(),
                    'description' => 'List of strings representation for "order_parts_relation" field'
                ],
                'order_parts_relation' => [
                    'type' => TicketOrderCategoryType::list(),
                    'alias' => 'orderPartsRelation',
                    'is_relation' => true,
                ],
                'status' => [
                    'type' => TicketStatusEnumType::nonNullType(),
                ],
                'case_id' => [
                    'type' => Type::int(),
                ],
                'translation' => [
                    'type' => TicketTranslationType::nonNullType(),
                    'is_relation' => true,
                ],
                'translations' => [
                    'type' => TicketTranslationType::nonNullList(),
                    'is_relation' => true,
                ],
                'can_create_order' => [
                    'type' => NonNullType::boolean(),
                    'always' => 'status',
                    'resolve' => static fn(Ticket $t): bool => $t->status->orderable(),
                    'description' => 'Determine if Order button should be displayed on Ticket card'
                ],
                'can_update_ticket' => [
                    'type' => NonNullType::boolean(),
                    'always' => 'status',
                    'resolve' => fn(Ticket $t): bool => $t->status->updatable()
                        && ($user = $this->getAuthUser()) instanceof Technician
                        && $user->is_certified,
                    'description' => 'Determine if ticket can be updated by technician'
                ],
            ]
        );
    }
}
