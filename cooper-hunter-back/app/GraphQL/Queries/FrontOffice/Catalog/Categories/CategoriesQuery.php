<?php

namespace App\GraphQL\Queries\FrontOffice\Catalog\Categories;

use App\GraphQL\Queries\Common\Catalog\Categories\BaseCategoriesQuery;
use App\GraphQL\Types\Catalog\Categories\CategoryType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Rebing\GraphQL\Support\SelectFields;

class CategoriesQuery extends BaseCategoriesQuery
{
    public function __construct()
    {
        $this->setMemberGuard();
    }

    public function type(): Type
    {
        return CategoryType::paginate();
    }

    public function args(): array
    {
        return array_merge(
            $this->paginationArgs(),
            parent::args()
        );
    }

    public function doResolve(
        $root,
        array $args,
        $context,
        ResolveInfo $info,
        SelectFields $fields
    ): LengthAwarePaginator
    {
        return $this->paginate(
            $this->getQuery($fields, $args)
                ->oldest('sort'),
            $args
        );
    }

    protected function initArgs(array $args): array
    {
        $args['active'] = true;

        return $args;
    }
}
