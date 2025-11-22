<?php


namespace App\GraphQL\Queries\BackOffice\Branches;


use App\GraphQL\Types\DownloadType;
use App\Permissions\Branches\BranchShowPermission;
use App\Services\Branches\BranchService;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;

class BranchesExportQuery extends BaseQuery
{
    public const NAME = 'branchesExport';
    public const PERMISSION = BranchShowPermission::KEY;

    public function __construct(private BranchService $service)
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return [];
    }

    public function type(): Type
    {
        return DownloadType::nonNullType();
    }

    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): array
    {
        return $this->service->export();
    }
}
