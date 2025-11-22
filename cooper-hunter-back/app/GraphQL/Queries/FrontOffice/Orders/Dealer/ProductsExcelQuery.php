<?php

namespace App\GraphQL\Queries\FrontOffice\Orders\Dealer;

use App\Entities\Messages\ResponseMessageEntity;
use App\GraphQL\Types\Messages\ResponseMessageType;
use App\Permissions\Orders\Dealer\CreatePermission;
use App\Repositories\Catalog\Product\ProductRepository;
use App\Repositories\Orders\Dealer\OrderRepository;
use App\Services\Catalog\ProductService;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;

class ProductsExcelQuery extends BaseQuery
{
    public const NAME = 'dealerProductOrderExcel';
    public const PERMISSION = CreatePermission::KEY;

    public function __construct(
        protected ProductRepository $repo,
        protected ProductService $serviceProduct,
        protected OrderRepository $repoOrder
    )
    {
        $this->setDealerGuard();
    }

    public function args(): array
    {
        return [];
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
            $products = $this->repo->getListForDealerOrder($this->user());

            return ResponseMessageEntity::success(
                $this->serviceProduct->generateExcelForDealerOrder($products)
            );
        } catch (\Throwable $e) {
            return ResponseMessageEntity::warning($e->getMessage());
        }
    }
}
