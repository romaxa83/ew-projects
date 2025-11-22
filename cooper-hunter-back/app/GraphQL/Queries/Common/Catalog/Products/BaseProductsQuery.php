<?php

namespace App\GraphQL\Queries\Common\Catalog\Products;

use App\GraphQL\Types\Catalog\Products\ProductType;
use App\GraphQL\Types\Enums\Catalog\CategoryTypeEnumType;
use App\GraphQL\Types\Enums\Catalog\Products\ProductUnitTypeEnumType;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Certificates\Certificate;
use App\Models\Catalog\Products\Product;
use App\Models\Catalog\Videos\VideoLink;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;

abstract class BaseProductsQuery extends BaseQuery
{
    public const NAME = 'products';

    public function args(): array
    {
        return array_merge(
            $this->paginationArgs(),
            $this->getSlugsArgs(),
            [
                'sort' => [
                    'type' => Type::string(),
                    'description' => 'Sorting by fields. Available fields: ' . implode(',', Product::ALLOWED_SORTING_FIELDS),
                    'defaultValue' => 'title-asc',
                ],
                'ids' => [
                    'type' => Type::listOf(
                        NonNullType::id()
                    ),
                    'rules' => [
                        'nullable',
                        'array',
                        Rule::exists(
                            Product::class,
                            'id'
                        )
                    ]
                ],
                'title' => [
                    'type' => Type::string(),
                    'description' => 'Filter by title.',
                ],
                'query' => [
                    'type' => Type::string(),
                    'description' => 'Filter by serial number.',
                ],
                'active' => [
                    'type' => Type::boolean()
                ],
                'category_id' => [
                    'type' => Type::id()
                ],
                'category_slug' => [
                    'type' => Type::string()
                ],
                'category_type' => [
                    'type' => CategoryTypeEnumType::type()
                ],
                'value_ids' => [
                    'type' => Type::listOf(Type::id()),
                    'description' => 'Filter products by ids values',
                ],
                'unit_type' => [
                    'type' => ProductUnitTypeEnumType::type()
                ],
            ]
        );
    }

    public function type(): Type
    {
        return ProductType::paginate();
    }

    public function doResolve(
        $root,
        array $args,
        $context,
        ResolveInfo $info,
        SelectFields $fields
    ): LengthAwarePaginator {
        return $this->paginate(
            $this->getQuery($fields, $args),
            $args
        );
    }

    protected function getQuery(SelectFields $fields, array $args): Product|Builder
    {
        return Product::query()
            ->cooper()
            ->select($fields->getSelect() ?: ['id'])
            ->addIsFavourite($this->user())
            ->filter($args)
            ->with($fields->getRelations())
            ->with(
                [
                    'certificates' => fn(BelongsToMany|Certificate $q) => $q
                        ->select(Certificate::TABLE . '.*')
                        ->addTypeName()
                ]
            )
            ->with(
                [
                    'videoLinks' => fn(BelongsToMany|VideoLink $q) => $q
                        ->with('translation')
                        ->with('group.translation')
                ]
            )
            ->with('manuals.group.translation')
            ->groupBy(Product::TABLE . '.id');
    }

    protected function rules(array $args = []): array
    {
        return $this->returnEmptyIfGuest(
            array_merge(
                $this->paginationRules(),
                $this->getSlugsRules(),
                [
                    'id' => ['nullable', 'integer'],
                    'title' => ['nullable', 'string'],
                    'category_id' => ['nullable', 'integer'],
                    'active' => ['nullable', 'boolean'],
                ]
            )
        );
    }
}
