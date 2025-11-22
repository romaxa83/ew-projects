<?php

namespace App\GraphQL\Queries\BackOffice\Catalog\Labels;

use App\GraphQL\Types\Catalog\Labels\LabelType;
use App\Permissions\Catalog\Labels\ListPermission;
use App\Repositories\Catalog\Labels\LabelRepository;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Rebing\GraphQL\Support\SelectFields;

class LabelsQuery extends BaseQuery
{
    public const NAME = 'labels';
    public const PERMISSION = ListPermission::KEY;

    public function __construct(protected LabelRepository $repo)
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return array_merge(
            $this->paginationArgs(),
            ['id' => Type::id()],
        );
    }

    public function type(): Type
    {
        return LabelType::paginate();
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
            $args,
            'sort'
        );
    }
}

