<?php

namespace App\GraphQL\Queries\BackOffice\Catalog\Products;

use App\GraphQL\Types\Catalog\Products\ProductKeywordType;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Products\Product;
use App\Permissions\Catalog\Products\ListPermission;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;

class ProductKeywordsQuery extends BaseQuery
{
    public const NAME = 'productKeywords';
    public const PERMISSION = ListPermission::KEY;

    public function __construct()
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return [
            'product_id' => [
                'type' => NonNullType::id(),
                'rules' => [Rule::exists(Product::class, 'id')],
            ],
            'id' => [
                'type' => Type::id(),
            ],
        ];
    }

    public function type(): Type
    {
        return ProductKeywordType::nonNullList();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): Collection {
        return Product::query()
            ->findOrFail($args['product_id'])
            ->keywords()
            ->filter($args)
            ->get();
    }
}