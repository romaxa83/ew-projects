<?php

namespace App\GraphQL\Queries\FrontOffice\Commercial;

use App\GraphQL\Types\Commercial\CommercialProjectType;
use App\GraphQL\Types\Enums\Commercial\CommercialProjectStatusEnumType;
use App\Permissions\Commercial\CommercialProjects\CommercialProjectListPermission;
use App\Repositories\Commercial\CommercialProjectRepository;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Rebing\GraphQL\Support\SelectFields;

class CommercialProjectsQuery extends BaseQuery
{
    public const NAME = 'commercialProjects';
    public const PERMISSION = CommercialProjectListPermission::KEY;

    public function __construct(protected CommercialProjectRepository $repo)
    {
        $this->setTechnicianGuard();
    }

    public function args(): array
    {
        return array_merge(
            parent::args(),
            [
                'name' => [
                    'type' => Type::string(),
                    'description' => 'Search by project name'
                ],
                'status' => [
                    'type' => CommercialProjectStatusEnumType::type(),
                    'description' => 'Filter by status',
                ],
            ],
        );
    }

    public function type(): Type
    {
        return CommercialProjectType::paginate();
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

        return $this->repo->forFrontPaginatorByUser($this->user(), [], $args);
    }
}
