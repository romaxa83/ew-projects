<?php

namespace App\GraphQL\Queries\FrontOffice\Orders\Dealer;

use App\Entities\Messages\ResponseMessageEntity;
use App\GraphQL\Types\Messages\ResponseMessageType;
use App\Models\Orders\Dealer\Order;
use App\Permissions\Orders\Dealer\CreatePermission;
use App\Repositories\Orders\Dealer\OrderRepository;
use App\Services\Orders\Dealer\OrderService;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;

class EstimateQuery extends BaseQuery
{
    public const NAME = 'dealerOrderEstimate';
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
            'id' => [
                'type' => Type::id(),
                'rules' => ['nullable', Rule::exists(Order::TABLE, 'id')],
                'description' => 'DealerOrderType ID'
            ],
        ];
    }

    public function type(): Type
    {
        return ResponseMessageType::nonNullType();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): ResponseMessageEntity
    {
        try {
            /** @var $order Order */
            $order = $this->repo->getBy('id', $args['id']);

            return ResponseMessageEntity::success(
                $this->service->generateAndSavePdf($order)
            );
        } catch (\Throwable $e) {
            return ResponseMessageEntity::warning($e->getMessage());
        }
    }
}
