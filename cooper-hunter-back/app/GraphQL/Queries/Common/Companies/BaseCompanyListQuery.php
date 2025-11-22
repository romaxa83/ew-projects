<?php

namespace App\GraphQL\Queries\Common\Companies;

use App\GraphQL\Types\Companies\CompanyForListType;
use App\GraphQL\Types\Enums\Companies\CompanyStatusEnumType;
use App\Permissions\Companies\CompanyListPermission;
use App\Repositories\Companies\CompanyRepository;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Support\Collection;
use Rebing\GraphQL\Support\SelectFields;

abstract class BaseCompanyListQuery extends BaseQuery
{
    public const NAME = 'companyList';
    public const PERMISSION = CompanyListPermission::KEY;

    public function __construct(protected CompanyRepository $repo)
    {
        $this->setQueryGuard();
    }

    abstract protected function setQueryGuard(): void;

    public function args(): array
    {
        return [
            'status' => CompanyStatusEnumType::type()
        ];
    }

    public function type(): Type
    {
        return CompanyForListType::list();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): Collection
    {
        return $this->repo->getAllObj(
            $fields->getSelect(),
            [],
            $args,
            ['business_name' => 'asc']
        );
    }
}


