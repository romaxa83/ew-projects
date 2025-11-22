<?php

namespace App\GraphQL\Queries\Common\Commercial;

use App\GraphQL\Types\Commercial\CommercialProjectUnitType;
use App\GraphQL\Types\NonNullType;
use App\Models\Commercial\CommercialProject;
use App\Permissions\Commercial\CommercialProjects\CommercialProjectListPermission;
use App\Repositories\Commercial\CommercialProjectUnitRepository;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;

abstract class BaseCommercialProjectUnitsQuery extends BaseQuery
{
    public const NAME = 'commercialProjectUnits';
    public const PERMISSION = CommercialProjectListPermission::KEY;

    public function __construct(protected CommercialProjectUnitRepository $repo)
    {
        $this->setQueryGuard();
    }

    abstract protected function setQueryGuard(): void;

    public function args(): array
    {
        $parent = parent::args();
        unset(
            $parent['created_at'],
            $parent['updated_at'],
        );

        return array_merge(
            $parent,
            [
                'commercial_project_id' => [
                    'type' =>  NonNullType::id(),
                    ['required', 'int', Rule::exists(CommercialProject::class, 'id')]
                ],
            ]
        );
    }

    public function type(): Type
    {
        return CommercialProjectUnitType::paginate();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): LengthAwarePaginator
    {
        $this->isTechnicianCommercial();

        return $this->repo->getAllPagination(
            $fields->getRelations(),
            $args,
            'sort'
        );
    }
}
