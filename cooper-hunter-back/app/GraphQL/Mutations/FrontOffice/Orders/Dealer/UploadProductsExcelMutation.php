<?php

namespace App\GraphQL\Mutations\FrontOffice\Orders\Dealer;

use App\GraphQL\Types\FileType;
use App\GraphQL\Types\Orders\Dealer\OrderType;
use App\Models\Orders\Dealer\Order;
use App\Permissions\Orders\Dealer\CreatePermission;
use App\Services\Orders\Dealer\OrderService;
use Core\GraphQL\Mutations\BaseMutation;
use Core\Traits\Auth\DealerInspector;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

class UploadProductsExcelMutation extends BaseMutation
{
    use DealerInspector;

    public const NAME = 'dealerOrderUploadProductsExcel';
    public const PERMISSION = CreatePermission::KEY;

    public function __construct(
        protected OrderService $service
    )
    {
        $this->setDealerGuard();
    }

    public function args(): array
    {
        return [
            'media' => [
                'type' => FileType::nonNullType(),
                'rules' => ['required', 'file'],
            ]
        ];
    }

    public function type(): Type
    {
        return OrderType::type();
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
        return makeTransaction(
            fn(): Order => $this->service->createFromFile($this->user(), $args['media'])
        );
    }
}
