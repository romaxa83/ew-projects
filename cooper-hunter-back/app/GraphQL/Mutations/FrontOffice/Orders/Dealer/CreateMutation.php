<?php

namespace App\GraphQL\Mutations\FrontOffice\Orders\Dealer;

use App\Dto\Orders\Dealer\OrderDto;
use App\GraphQL\InputTypes\Orders\Dealer;
use App\GraphQL\Types\Orders\Dealer\OrderType;
use App\Models\Orders\Dealer\Order;
use App\Permissions\Orders\Dealer\CreatePermission;
use App\Services\Orders\Dealer\OrderService;
use Core\GraphQL\Mutations\BaseMutation;
use Core\Traits\Auth\DealerInspector;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class CreateMutation extends BaseMutation
{
    use DealerInspector;

    public const NAME = 'dealerOrderCreate';
    public const PERMISSION = CreatePermission::KEY;

    public function __construct(protected OrderService $service)
    {
        $this->setDealerGuard();
    }

    public function args(): array
    {
        return [];
    }

    public function type(): Type
    {
        return OrderType::nonNullType();
    }

    /**
     * @throws Throwable
     */
    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): Order
    {
        $this->isNotForMainDealer();

        $model = makeTransaction(
            fn(): Order => $this->service->create($this->user())
        );

        return $model;
    }

    protected function rules(array $args = []): array
    {
        return [];
    }
}
