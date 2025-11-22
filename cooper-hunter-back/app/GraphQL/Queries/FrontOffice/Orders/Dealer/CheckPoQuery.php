<?php

namespace App\GraphQL\Queries\FrontOffice\Orders\Dealer;

use App\Models\Orders\Dealer\Order;
use App\Permissions\Orders\Dealer\CreatePermission;
use App\Repositories\Orders\Dealer\OrderRepository;
use App\Services\Orders\Dealer\OrderService;
use App\Traits\GraphQL\Order\Dealer\InitArgsForFilter;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;

class CheckPoQuery extends BaseQuery
{
    use InitArgsForFilter;

    public const NAME = 'dealerOrderCheckPo';
    public const PERMISSION = CreatePermission::KEY;

    public function __construct(
        protected OrderRepository $repo,
        protected OrderService $service
    )
    {
        $this->setDealerGuard();
    }

    public function args(): array
    {
        return [
            'po' => [
                'type' => Type::string(),
                'rules' => ['required'],
            ],
        ];
    }

    public function type(): Type
    {
        return Type::boolean();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): bool
    {
        $dealerIds = $this->user()->company->dealers->pluck('id')->toArray();

        return Order::query()
            ->whereIn('dealer_id', $dealerIds)
            ->where('po', $args['po'])
            ->exists();
    }
}
