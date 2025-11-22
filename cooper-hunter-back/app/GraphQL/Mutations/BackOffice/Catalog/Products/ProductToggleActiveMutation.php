<?php

namespace App\GraphQL\Mutations\BackOffice\Catalog\Products;

use App\GraphQL\Types\Catalog\Products;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Products\Product;
use App\Permissions\Catalog\Products\UpdatePermission;
use App\Services\Catalog\ProductService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;

class ProductToggleActiveMutation extends BaseMutation
{
    public const NAME = 'productToggleActive';
    public const PERMISSION = UpdatePermission::KEY;

    public function __construct(protected ProductService $service)
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return Products\ProductType::type();
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

    public function doResolve($root, array $args, $context, ResolveInfo $info, SelectFields $fields): Model
    {
        return $this->service->toggleActive(
            Product::find($args['id'])
        );
    }
}

