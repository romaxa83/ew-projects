<?php


namespace App\GraphQL\Queries\Common\Branches;


use App\GraphQL\Types\Branches\BranchType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Database\Eloquent\Collection;
use Rebing\GraphQL\Support\SelectFields;

abstract class BaseBranchesListQuery extends BaseBranchesQuery
{
    public const NAME = 'branchesList';

    public function type(): Type
    {
        return BranchType::list();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): Collection {
        return $this->service->list($args, $this->user());
    }
}
