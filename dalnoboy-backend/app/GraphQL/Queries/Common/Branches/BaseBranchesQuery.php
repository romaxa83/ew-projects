<?php


namespace App\GraphQL\Queries\Common\Branches;


use App\GraphQL\Types\Branches\BranchType;
use App\Models\Branches\Branch;
use App\Permissions\Branches\BranchShowPermission;
use App\Services\Branches\BranchService;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Rebing\GraphQL\Support\SelectFields;

abstract class BaseBranchesQuery extends BaseQuery
{
    public const NAME = 'branches';
    public const PERMISSION = BranchShowPermission::KEY;

    public function __construct(protected BranchService $service)
    {
        $this->setQueryGuard();
    }

    public function args(): array
    {
        return $this->buildArgs(Branch::ALLOWED_ORDERED_FIELDS, ['address', 'city', 'region', 'name']);
    }

    public function type(): Type
    {
        return BranchType::paginate();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): LengthAwarePaginator|Collection {
        return $this->service->show($args);
    }

    abstract protected function setQueryGuard(): void;
}
