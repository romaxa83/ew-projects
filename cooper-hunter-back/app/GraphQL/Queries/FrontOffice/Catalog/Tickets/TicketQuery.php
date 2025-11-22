<?php

namespace App\GraphQL\Queries\FrontOffice\Catalog\Tickets;

use App\GraphQL\Types\Catalog\Tickets\TicketType;
use App\GraphQL\Types\NonNullType;
use App\Permissions\Catalog\Products\ListPermission;
use App\Services\Catalog\Tickets\TicketService;
use Closure;
use Core\Enums\Messages\AuthorizationMessageEnum;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Rebing\GraphQL\Support\SelectFields;

class TicketQuery extends BaseQuery
{
    public const NAME = 'ticket';
    public const PERMISSION = ListPermission::KEY;

    public function __construct(private TicketService $service)
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
        if (!$this->can(static::PERMISSION)) {
            return false;
        }

        if (!$this->user()->is_certified) {
            $this->authMessage = AuthorizationMessageEnum::NO_PERMISSION;
            return false;
        }

        return true;
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => Type::id(),
                'description' => 'Filter by ticket id.'
            ],
            'serial_number' => [
                'type' => NonNullType::string(),
                'rules' => [
                    'required',
                    'string',
                    'max:255',
                ]
            ],
            'page' => [
                'type' => Type::int(),
                'defaultValue' => 1
            ],
            'per_page' => [
                'type' => Type::int(),
                'defaultValue' => config('queries.default.pagination.per_page')
            ]
        ];
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
        return $this->service->getList($args);
    }
}
