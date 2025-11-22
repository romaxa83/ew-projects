<?php

namespace App\GraphQL\Queries\FrontOffice\Catalog\Search;

use App\GraphQL\Types\Catalog\Products\ProductType;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Certificates\Certificate;
use App\Models\Catalog\Products\Product;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Rebing\GraphQL\Support\SelectFields;

class ProductSearchQuery extends BaseQuery
{
    public const NAME = 'productSearch';

    public function type(): Type
    {
        return ProductType::type();
    }

    public function args(): array
    {
        return [
            'serial_number' => NonNullType::string(),
        ];
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): ?Product {
        return Product::query()
            ->select($fields->getSelect() ?: ['id'])
            ->where('active', true)
            ->whereHas(
                'serialNumbers',
                static fn(Builder $b) => $b->where('serial_number', $args['serial_number'])
            )
            ->with($fields->getRelations())
            ->with(
                [
                    'certificates' => fn(BelongsToMany|Certificate $q) => $q
                        ->select(Certificate::TABLE . '.*')
                        ->addTypeName()
                ]
            )
            ->first();
    }

    protected function rules(array $args = []): array
    {
        return [
            'serial_number' => ['required', 'string', 'min:3'],
        ];
    }
}
