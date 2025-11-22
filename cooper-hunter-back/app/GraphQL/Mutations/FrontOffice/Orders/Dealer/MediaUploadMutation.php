<?php

namespace App\GraphQL\Mutations\FrontOffice\Orders\Dealer;

use App\GraphQL\Types\FileType;
use App\GraphQL\Types\NonNullType;
use App\GraphQL\Types\Orders\Dealer\OrderType;
use App\Models\Orders\Dealer\Order;
use App\Permissions\Orders\Dealer\UpdatePermission;
use App\Repositories\Orders\Dealer\OrderRepository;
use Core\GraphQL\Mutations\BaseMutation;
use Core\Traits\Auth\DealerInspector;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

class MediaUploadMutation extends BaseMutation
{
    use DealerInspector;

    public const NAME = 'dealerOrderUploadMedia';
    public const PERMISSION = UpdatePermission::KEY;

    public function __construct(
        protected OrderRepository $repo
    )
    {
        $this->setDealerGuard();
    }

    public function type(): Type
    {
        return OrderType::type();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'rules' => ['required', 'int', Rule::exists(Order::class, 'id')],
                'description' => 'DealerOrderType ID'
            ],
            'media' => [
                'type' => NonNullType::listOf(FileType::nonNullType()),
            ]
        ];
    }

    /**
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): Order
    {
//        dd($args);
        $this->isNotForMainDealer();

        /** @var $order Order */
        $order = $this->repo->getBy('id', $args['id']);

        $this->canUpdateOrder($order);

        foreach ($args['media'] as $media){
            $order->addMedia($media)
                ->toMediaCollection(Order::MEDIA_COLLECTION_NAME);
        }

        return $order;
    }

    protected function rules(array $args = []): array
    {
        return [
            'media.*' => 'required|mimes:pdf,xls,xlsx,docx,doc |max:10240'
        ];
    }
}
