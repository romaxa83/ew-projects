<?php

namespace App\GraphQL\Mutations\BackOffice\Catalog\Products;

use App\Entities\Messages\ResponseMessageEntity;
use App\GraphQL\Types\Messages\ResponseMessageType;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Products\Product;
use App\Permissions\Catalog\Products\DeletePermission;
use App\Services\Catalog\ProductService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class ProductDeleteMutation extends BaseMutation
{
    public const NAME = 'productDelete';
    public const PERMISSION = DeletePermission::KEY;

    public function __construct(protected ProductService $service)
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return ResponseMessageType::nonNullType();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'rules' => [
                    'required',
                    'int',
                    Rule::exists(Product::class, 'id')
                ]
            ]
        ];
    }

    public function doResolve(
        $root,
        array $args,
        $context,
        ResolveInfo $info,
        SelectFields $fields
    ): ResponseMessageEntity {
        try {
            $this->service->remove(
                Product::find($args['id'])
            );

            return ResponseMessageEntity::success(__('messages.catalog.product.actions.delete.success.one-entity'));
        } catch (Throwable) {
            return ResponseMessageEntity::fail(__('Oops, something went wrong!'));
        }
    }
}


