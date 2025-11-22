<?php

namespace App\GraphQL\Queries\Common\Companies;

use App\GraphQL\Types\Companies\CompanyType;
use App\GraphQL\Types\Enums\Companies\CompanyStatusEnumType;
use App\Permissions\Companies\CompanyListPermission;
use App\Repositories\Companies\CompanyRepository;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Rebing\GraphQL\Support\SelectFields;

abstract class BaseCompaniesQuery extends BaseQuery
{
    public const NAME = 'companies';
    public const PERMISSION = CompanyListPermission::KEY;

    public function __construct(protected CompanyRepository $repo)
    {
        $this->setQueryGuard();
    }

    abstract protected function setQueryGuard(): void;

    public function args(): array
    {
        return array_merge(
            $this->paginationArgs(),
            ['id' => Type::id()],
            ['corporation_id' => Type::id()],
            ['status' => CompanyStatusEnumType::type()]
        );
    }

    public function type(): Type
    {
        return CompanyType::paginate();
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

