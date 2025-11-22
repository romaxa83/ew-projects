<?php

namespace App\GraphQL\Queries\BackOffice\Catalog\Features\Features;

use App\GraphQL\Types\Catalog\Features\Features\FeatureType;
use App\Models\Catalog\Features\Feature;
use App\Permissions\Catalog\Features\Features\ListPermission;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Rebing\GraphQL\Support\SelectFields;

class FeaturesQuery extends BaseQuery
{
    public const NAME = 'features';
    public const PERMISSION = ListPermission::KEY;

    public function __construct()
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return array_merge(
            $this->paginationArgs(),
            $this->sortArgs(),
            [
                'id' => Type::id(),
                'title' => Type::string(),
                'active' => Type::boolean(),
            ]
        );
    }

    public function type(): Type
    {
        return FeatureType::paginate();
    }

    public function doResolve(
        $root,
        array $args,
        $context,
        ResolveInfo $info,
        SelectFields $fields
    ): LengthAwarePaginator {
        return $this->paginate(
            Feature::query()
                ->select($fields->getSelect() ?: ['id'])
                ->filter($args)
                ->with($fields->getRelations())
                ->latest('sort'),
            $args
        );
    }

    protected function rules(array $args = []): array
    {
        return array_merge(
            $this->paginationRules(),
            [
                'id' => ['nullable', 'integer'],
                'title' => ['nullable', 'string'],
                'active' => ['nullable', 'boolean'],
            ]
        );
    }
}



