<?php


namespace App\GraphQL\Queries\Common\Drivers;


use App\GraphQL\Types\Drivers\DriverType;
use App\Models\Drivers\Driver;
use App\Permissions\Drivers\DriverShowPermission;
use App\Services\Drivers\DriverService;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Rebing\GraphQL\Support\SelectFields;

abstract class BaseDriversQuery extends BaseQuery
{
    public const NAME = 'drivers';
    public const PERMISSION = DriverShowPermission::KEY;

    public function __construct(private DriverService $service)
    {
        $this->setQueryGuard();
    }

    abstract protected function setQueryGuard(): void;

    public function args(): array
    {
        return $this->buildArgs(
            Driver::ALLOWED_SORTING_FIELDS,
            [
                'full_name',
                'email',
                'phone',
                'client_full_name',
                'client_phone',
            ]
        );
    }

    public function type(): Type
    {
        return DriverType::paginate();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): LengthAwarePaginator {
        return $this->service->show($args, $fields->getRelations());
    }
}
