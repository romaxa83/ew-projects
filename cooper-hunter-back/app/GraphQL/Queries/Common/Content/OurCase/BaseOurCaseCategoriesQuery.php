<?php

namespace App\GraphQL\Queries\Common\Content\OurCase;

use App\GraphQL\Types\Content\OurCase\OurCaseCategoryType;
use App\Models\Content\OurCases\OurCaseCategory;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Rebing\GraphQL\Support\SelectFields;

abstract class BaseOurCaseCategoriesQuery extends BaseQuery
{
    public const NAME = 'ourCasesCategories';

    public function type(): Type
    {
        return OurCaseCategoryType::list();
    }

    public function args(): array
    {
        return [];
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): Collection {
        return $this->getQuery($args, $fields)
            ->get();
    }

    protected function getQuery(array $args, SelectFields $fields): Builder|OurCaseCategory
    {
        return OurCaseCategory::query()
            ->filter($args)
            ->with($fields->getRelations())
            ->withCount(
                [
                    'cases' => static fn(Builder $b) => $b->where('active', true)
                ]
            )
            ->latest('sort');
    }
}
