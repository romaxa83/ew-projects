<?php

namespace App\GraphQL\Mutations\FrontOffice\Orders\Dealer;

use App\GraphQL\Types\NonNullType;
use App\GraphQL\Types\Orders\Dealer\OrderType;
use App\Models\Media\Media;
use App\Models\Orders\Dealer\Order;
use App\Permissions\Orders\Dealer\UpdatePermission;
use Core\GraphQL\Mutations\BaseMutation;
use Core\Traits\Auth\DealerInspector;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;

class MediaDeleteMutation extends BaseMutation
{
    use DealerInspector;

    public const NAME = 'dealerOrderDeleteMedia';
    public const PERMISSION = UpdatePermission::KEY;

    public function __construct()
    {
        $this->setDealerGuard();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'rules' => ['required', 'int', Rule::exists(Media::class, 'id')],
                'description' => 'Media ID'
            ],
        ];
    }

    public function type(): Type
    {
        return OrderType::type();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): Order
    {
        $this->isNotForMainDealer();

        $media = Media::query()->where('id', $args['id'])->firstOrFail();

        $order = $media->model;

        $this->canUpdateOrder($order);

        $media->delete();

        return $order;
    }
}
