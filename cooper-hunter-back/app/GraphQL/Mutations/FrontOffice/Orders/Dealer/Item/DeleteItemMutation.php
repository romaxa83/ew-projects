<?php

namespace App\GraphQL\Mutations\FrontOffice\Orders\Dealer\Item;

use App\GraphQL\Types\NonNullType;
use App\Models\Orders\Dealer\Item;
use App\Permissions\Orders\Dealer\UpdatePermission;
use App\Repositories\Orders\Dealer\OrderItemRepository;
use App\Services\Orders\Dealer\OrderItemService;
use Core\GraphQL\Mutations\BaseMutation;
use Core\Traits\Auth\DealerInspector;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class DeleteItemMutation extends BaseMutation
{
    use DealerInspector;

    public const NAME = 'dealerOrderDeleteItem';
    public const PERMISSION = UpdatePermission::KEY;

    public function __construct(
        protected OrderItemService $service,
        protected OrderItemRepository $repo,
    )
    {
        $this->setDealerGuard();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'rules' => ['required', Rule::exists(Item::TABLE, 'id')],
                'description' => 'ItemType ID'
            ],
        ];
    }

    public function type(): Type
    {
        return NonNullType::boolean();
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
    ): bool
    {
        $this->isNotForMainDealer();

        /** @var $model Item */
        $model = $this->repo->getBy('id', $args['id']);

        $this->canUpdateOrder($model->order);

        return $this->service->delete($model);
    }
}
