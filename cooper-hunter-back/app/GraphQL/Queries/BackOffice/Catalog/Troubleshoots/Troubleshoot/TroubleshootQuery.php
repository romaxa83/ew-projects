<?php

namespace App\GraphQL\Queries\BackOffice\Catalog\Troubleshoots\Troubleshoot;

use App\GraphQL\Types\Catalog\Troubleshoots\Troubleshoots;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Products\Product;
use App\Models\Catalog\Troubleshoots\Troubleshoot;
use App\Permissions\Catalog\Troubleshoots\Troubleshoot\ListPermission;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;

class TroubleshootQuery extends BaseQuery
{
    public const NAME = 'troubleshoots';
    public const PERMISSION = ListPermission::KEY;

    public function __construct()
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return array_merge(
            $this->sortArgs(),
            [
                'product_id' => NonNullType::id(),
                'id' => Type::id(),
                'name' => Type::string(),
                'active' => Type::boolean(),
                'group_id' => Type::id(),
            ]
        );
    }

    public function type(): Type
    {
        return Troubleshoots\TroubleshootType::list();
    }

    public function doResolve(
        $root,
        array $args,
        $context,
        ResolveInfo $info,
        SelectFields $fields
    ): Collection {
        return Troubleshoot::query()
            ->select($fields->getSelect() ?: ['id'])
            ->whereHas(
                'group',
                fn (Builder $group) => $group->whereHas(
                    'products',
                    fn (Builder $products) => $products->where('product_id', $args['product_id'])
                )
            )
            ->filter($args)
            ->with($fields->getRelations())
            ->latest('sort')
            ->get();
    }

    protected function rules(array $args = []): array
    {
        return [
            'id' => ['nullable', 'integer'],
            'product_id' => ['required', 'integer', Rule::exists(Product::TABLE, 'id')],
            'name' => ['nullable', 'string'],
            'active' => ['nullable', 'boolean'],
            'group_id' => ['nullable', 'integer'],
        ];
    }
}
