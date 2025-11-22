<?php

namespace App\GraphQL\Queries\Common\Content\OurCase;

use App\GraphQL\Types\Content\OurCase\OurCaseType;
use App\Models\Content\OurCases\OurCase;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Rebing\GraphQL\Support\SelectFields;

abstract class BaseOurCaseQuery extends BaseQuery
{
    public const NAME = 'ourCases';

    public function args(): array
    {
        return array_merge(
            [
                'id' => [
                    'name' => 'id',
                    'type' => Type::id()
                ],
                'our_case_category_id' => [
                    'type' => Type::id(),
                ],
                'our_case_category_slug' => [
                    'type' => Type::string(),
                ],
            ],
            parent::args(),
            $this->getSlugArgs()
        );
    }

    public function type(): Type
    {
        return OurCaseType::paginate();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): LengthAwarePaginator {
        return $this->paginate(
            $this->getQuery($args, $fields),
            $args
        );
    }

    protected function getQuery(
        array $args,
        SelectFields $fields
    ): OurCase|Builder {
        return OurCase::query()
            ->filter($args)
            ->with($fields->getRelations())
            ->latest('sort');
    }
}
