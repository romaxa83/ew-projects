<?php

namespace App\GraphQL\Queries\Common\Orders\Dealer;

use App\GraphQL\Types\Enums\Orders\Dealer\OrderStatusTypeEnum;
use App\GraphQL\Types\Orders\Dealer\OrderType;
use App\Permissions\Orders\Dealer\ListPermission;
use App\Repositories\Orders\Dealer\OrderRepository;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Rebing\GraphQL\Support\SelectFields;

abstract class BaseOrdersQuery extends BaseQuery
{
    public const NAME = 'dealerOrders';
    public const PERMISSION = ListPermission::KEY;

    public function __construct(protected OrderRepository $repo)
    {
        $this->setQueryGuard();
    }

    abstract protected function setQueryGuard(): void;

    public function args(): array
    {
        return array_merge(
            $this->paginationArgs(),
            ['id' => Type::id()],
            ['po' => Type::string()],
            ['status' => OrderStatusTypeEnum::type()],
            ['statuses' => Type::listOf(OrderStatusTypeEnum::type())],
            ['company_id' => Type::id()],
            ['location_id' => Type::id()]
        );
    }

    public function type(): Type
    {
        return OrderType::paginate();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): LengthAwarePaginator
    {

        return $this->repo->getAllPagination(
            $fields->getRelations(),
            $args
        );
    }
}
