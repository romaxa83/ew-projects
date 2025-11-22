<?php

namespace App\GraphQL\Queries\Common\Commercial;

use App\Entities\Messages\ResponseMessageEntity;
use App\GraphQL\Types\Messages\ResponseMessageType;
use App\GraphQL\Types\NonNullType;
use App\Models\Commercial\CommercialProject;
use App\Permissions\Commercial\CommercialProjects\CommercialProjectListPermission;
use App\Repositories\Commercial\CommercialProjectRepository;
use App\Services\Commercial\CommercialProjectUnitService;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;

abstract class BaseCommercialProjectUnitsExcelQuery extends BaseQuery
{
    public const NAME = 'commercialProjectUnitsExcel';
    public const PERMISSION = CommercialProjectListPermission::KEY;

    public function __construct(
        protected CommercialProjectRepository $repo,
        protected CommercialProjectUnitService $service,
    )
    {
        $this->setQueryGuard();
    }

    abstract protected function setQueryGuard(): void;

    public function args(): array
    {
        return [
            'commercial_project_id' => [
                'type' =>  NonNullType::id(),
                ['required', 'int', Rule::exists(CommercialProject::class, 'id')]
            ],
        ];
    }

    public function type(): Type
    {
        return ResponseMessageType::nonNullType();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): ResponseMessageEntity
    {
        $this->isTechnicianCommercial();

        try {
            /** @var $model CommercialProject */
            $model = $this->repo->getByFields(['id' => $args['commercial_project_id']],['units.product'],true);

            return ResponseMessageEntity::success(
                $this->service->generateExcel($model)
            );
        } catch (\Throwable $e) {
            return ResponseMessageEntity::warning($e->getMessage());
        }
    }
}

