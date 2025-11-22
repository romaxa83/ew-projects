<?php

namespace App\GraphQL\Queries\BackOffice\Companies;

use App\GraphQL\Types\Companies\CorporationType;
use App\Permissions\Companies\CompanyListPermission;
use App\Repositories\Companies\CorporationRepository;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Support\Collection;
use Rebing\GraphQL\Support\SelectFields;

class CorporationListQuery extends BaseQuery
{
    public const NAME = 'corporationList';
    public const PERMISSION = CompanyListPermission::KEY;

    public function __construct(protected CorporationRepository $repo)
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return CorporationType::list();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): Collection
    {
        return $this->repo->getAllObj($fields->getSelect());
    }
}
