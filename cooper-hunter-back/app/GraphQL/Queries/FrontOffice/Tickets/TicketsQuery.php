<?php

namespace App\GraphQL\Queries\FrontOffice\Tickets;

use App\GraphQL\Types\Catalog\Tickets\TicketType;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Tickets\Ticket;
use App\Permissions\Catalog\Tickets\CreatePermission;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;

class TicketsQuery extends BaseQuery
{
    public const NAME = 'tickets';
    public const PERMISSION = CreatePermission::KEY;

    public function __construct()
    {
        $this->setTechnicianGuard();
    }

    public function args(): array
    {
        return array_merge(
            $this->getIdArgs(),
            [
                'serial_number' => [
                    'type' => NonNullType::string(),
                    'rules' => ['required', 'string', Rule::exists(Ticket::class, 'serial_number')]
                ],
            ]
        );
    }

    public function type(): Type
    {
        return TicketType::paginate();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): LengthAwarePaginator {
        return $this->paginate(
            Ticket::query()
                ->select($fields->getSelect() ?: ['id'])
                ->filter($args)
                ->with($fields->getRelations()),
            $args,
        );
    }
}