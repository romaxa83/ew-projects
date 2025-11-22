<?php

namespace App\GraphQL\Mutations\BackOffice\Catalog\Products;

use App\Dto\Catalog\Products\ProductDto;
use App\GraphQL\InputTypes\Catalog\Products\ProductInput;
use App\GraphQL\Types\Catalog\Products;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Products\Product;
use App\Permissions\Catalog\Products\UpdatePermission;
use App\Services\Catalog\ProductService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class ProductUpdateMutation extends BaseMutation
{
    public const NAME = 'productUpdate';
    public const PERMISSION = UpdatePermission::KEY;

    public function __construct(private ProductService $service)
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
                    'integer',
                    Rule::exists(Product::class, 'id')
                ]
            ],
            'product' => [
                'type' => ProductInput::nonNullType()
            ]
        ];
    }

    /**
     * @param $root
     * @param array $args
     * @param $context
     * @param ResolveInfo $info
     * @param SelectFields $fields
     * @return Product
     * @throws Throwable
     */
    public function doResolve($root, array $args, $context, ResolveInfo $info, SelectFields $fields): Product
    {
        return makeTransaction(
            fn() => $this->service->update(
                ProductDto::byArgs($args['product']),
                Product::find($args['id'])
            )
        );
    }

    protected function rules(array $args = []): array
    {
        return [
            'product.guid' => ['nullable', 'uuid', Rule::unique(Product::class, 'guid')->ignore($args['id'])],
            'product.slug' => ['required', 'string', Rule::unique(Product::class, 'slug')->ignore($args['id'])],
        ];
    }
}
