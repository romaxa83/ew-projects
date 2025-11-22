<?php


namespace App\GraphQL\Queries\Common\Inspections;


use App\GraphQL\Types\Enums\Vehicles\VehicleFormEnumType;
use App\GraphQL\Types\Inspections\InspectionType;
use App\Models\Users\User;
use App\Permissions\Inspections\InspectionShowPermission;
use App\Services\Inspections\InspectionService;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;

abstract class BaseInspectionsQuery extends BaseQuery
{
    public const NAME = 'inspections';
    public const PERMISSION = InspectionShowPermission::KEY;

    protected $depthOutput = 6;

    public function __construct(private InspectionService $service)
    {
        $this->setQueryGuard();
    }

    abstract protected function setQueryGuard(): void;

    public function args(): array
    {
        return array_merge(
            $this->buildArgs(['fields' => ['created_at'], 'default_value' => ['created_at-desc']]),
            [
                'state_number' => [
                    'type' => Type::string(),
                    'description' => 'Min three later',
                    'rules' => [
                        'nullable',
                        'string',
                        'min:3'
                    ]
                ],
                'vehicle_form' => [
                    'type' => VehicleFormEnumType::type(),
                ],
                'without_connection' => [
                    'type' => Type::boolean(),
                    'description' => 'Filter by inspections which not linked to another inspections'
                ],
                'moderated' => [
                    'type' => Type::boolean(),
                ],
                'date_from' => [
                    'type' => Type::string(),
                    'description' => 'Format: Y-m-d',
                    'rules' => [
                        'nullable',
                        'date'
                    ]
                ],
                'date_to' => [
                    'type' => Type::string(),
                    'description' => 'Format: Y-m-d',
                    'rules' => [
                        'nullable',
                        'date'
                    ]
                ],
                'inspector' => [
                    'type' => Type::int(),
                    'rules' => [
                        Rule::exists(User::class, 'id')
                    ],
                ],
            ]
        );
    }

    public function type(): Type
    {
        return InspectionType::paginate();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): LengthAwarePaginator {
        return $this->service->list($args, $fields->getSelect(), $fields->getRelations(), $this->user());
    }
}
