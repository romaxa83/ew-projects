<?php

namespace App\GraphQL\Queries\BackOffice\Dealers;

use App\GraphQL\Types\Dealers\DealerType;
use App\Permissions\Dealers\DealerListPermission;
use App\Repositories\Dealers\DealerRepository;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Rebing\GraphQL\Support\SelectFields;

class DealersQuery extends BaseQuery
{
    public const NAME = 'dealers';
    public const PERMISSION = DealerListPermission::KEY;

    public function __construct(protected DealerRepository $repo)
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return array_merge(
            $this->paginationArgs(),
            ['id' => Type::id()],
            [
                'company_id' => [
                    'type' => Type::id(),
                    'description' => 'CompanyType ID'
                ],
            ]
        );
    }

    public function type(): Type
    {
        return DealerType::paginate();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): LengthAwarePaginator
    {
        return $this->repo->getAllPagination(
            $fields->getRelations(),
            $args
        );
    }
}
