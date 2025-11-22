<?php

namespace App\GraphQL\Mutations\BackOffice\Catalog\Products\Keywords;

use App\GraphQL\Types\Catalog\Products\ProductType;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Products\Product;
use App\Permissions\Catalog\Products\UpdatePermission;
use App\Services\Catalog\Products\ProductKeywordService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class ProductKeywordsModifyMutation extends BaseMutation
{
    public const NAME = 'productKeywordsModify';
    public const PERMISSION = UpdatePermission::KEY;

    public function __construct(private ProductKeywordService $service)
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return [
            'product_id' => [
                'type' => NonNullType::id(),
                'rules' => ['required', 'int', Rule::exists(Product::class, 'id')],
            ],
            'keywords' => [
                'type' => Type::listOf(NonNullType::string()),
                'description' => 'An empty value will remove all keywords.',
                'rules' => ['nullable', 'array'],
            ],
        ];
    }

    public function type(): Type
    {
        return ProductType::nonNullType();
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
    ): Product {
        return makeTransaction(
            fn() => $this->service->modify(
                Product::find($args['product_id']),
                $args['keywords'] ?? [],
            )
        );
    }
}