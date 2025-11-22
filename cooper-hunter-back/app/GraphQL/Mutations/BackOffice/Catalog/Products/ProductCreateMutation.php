<?php

namespace App\GraphQL\Mutations\BackOffice\Catalog\Products;

use App\Dto\Catalog\Products\ProductDto;
use App\GraphQL\InputTypes\Catalog\Products\ProductInput;
use App\GraphQL\Types\Catalog\Products\ProductType;
use App\Models\Catalog\Products\Product;
use App\Permissions\Catalog\Products\CreatePermission;
use App\Services\Catalog\ProductService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class ProductCreateMutation extends BaseMutation
{
    public const NAME = 'productCreate';
    public const PERMISSION = CreatePermission::KEY;

    public function __construct(private ProductService $service)
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return ProductType::type();
    }

    public function args(): array
    {
        return [
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
    public function doResolve(
        $root,
        array $args,
        $context,
        ResolveInfo $info,
        SelectFields $fields
    ): Product
    {
        return makeTransaction(
            fn() => $this->service->create(
                ProductDto::byArgs($args['product'])
            )
        );
    }

    protected function rules(array $args = []): array
    {
        return [
            'product.guid' => ['nullable', 'uuid', Rule::unique(Product::class, 'guid')],
            'product.slug' => ['required', 'string', Rule::unique(Product::class, 'slug')],
        ];
    }
}
