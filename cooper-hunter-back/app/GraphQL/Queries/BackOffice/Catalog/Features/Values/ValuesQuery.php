<?php

namespace App\GraphQL\Queries\BackOffice\Catalog\Features\Values;

use App\GraphQL\Types\Catalog\Features\Values\ValueType;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Features\Feature;
use App\Models\Catalog\Features\Value;
use App\Permissions\Catalog\Features\Values\ListPermission;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;

class ValuesQuery extends BaseQuery
{
    public const NAME = 'featureValues';
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
                'feature_id' => NonNullType::id(),
                'value_id' => Type::id(),
                'title' => Type::string(),
                'active' => Type::boolean(),
            ]
        );
    }

    public function type(): Type
    {
        return ValueType::list();
    }

    public function doResolve(
        $root,
        array $args,
        $context,
        ResolveInfo $info,
        SelectFields $fields
    ): Collection {
        return Feature::query()
            ->whereKey($args['feature_id'])
            ->firstOrFail()
            ->values()
            ->select($fields->getSelect() ?: ['id'])
            ->filter($args)
            ->with($fields->getRelations())
            ->latest('sort')
            ->get();
    }

    protected function rules(array $args = []): array
    {
        return [
            'feature_id' => ['required', 'integer', Rule::exists(Feature::TABLE, 'id')],
            'value_id' => [
                'nullable',
                'integer',
                Rule::exists(Value::TABLE, 'id')
                    ->where('feature_id', $args['feature_id'])
            ],
            'title' => ['nullable', 'string'],
            'active' => ['nullable', 'boolean'],
        ];
    }
}


