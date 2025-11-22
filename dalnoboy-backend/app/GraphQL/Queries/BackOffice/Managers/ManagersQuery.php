<?php


namespace App\GraphQL\Queries\BackOffice\Managers;


use App\GraphQL\Types\Managers\ManagerType;
use App\Models\Managers\Manager;
use App\Permissions\Managers\ManagerShowPermission;
use App\Services\Managers\ManagerService;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Rebing\GraphQL\Support\SelectFields;

class ManagersQuery extends BaseQuery
{
    public const NAME = 'managers';
    public const PERMISSION = ManagerShowPermission::KEY;

    public function __construct(private ManagerService $service)
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return $this->buildArgs(
            Manager::ALLOWED_SORTING_FIELDS,
            [
                'first_name',
                'last_name',
                'second_name',
                'full_name',
                'phone',
            ]
        );
    }

    public function type(): Type
    {
        return ManagerType::paginate();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): LengthAwarePaginator {
        return $this->service->show($args);
    }
}
