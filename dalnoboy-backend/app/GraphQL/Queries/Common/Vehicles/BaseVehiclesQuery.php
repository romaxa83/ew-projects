<?php


namespace App\GraphQL\Queries\Common\Vehicles;


use App\GraphQL\Types\Enums\Vehicles\VehicleFormEnumType;
use App\GraphQL\Types\Vehicles\VehicleType;
use App\Models\Vehicles\Vehicle;
use App\Permissions\Vehicles\VehicleShowPermission;
use App\Services\Vehicles\VehicleService;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Rebing\GraphQL\Support\SelectFields;

abstract class BaseVehiclesQuery extends BaseQuery
{
    public const NAME = 'vehicles';
    public const PERMISSION = VehicleShowPermission::KEY;

    public function __construct(private VehicleService $service)
    {
        $this->setQueryGuard();
    }

    public function args(): array
    {
        $args = array_merge(
            $this->buildArgs(Vehicle::ALLOWED_SORTING_FIELDS),
            [
                'state_number' => [
                    'type' => Type::string(),
                ],
                'vin' => [
                    'type' => Type::string(),
                ],
                'form' => [
                    'type' => VehicleFormEnumType::type()
                ],
                'class_id' => [
                    'type' => Type::id(),
                ],
                'type_id' => [
                    'type' => Type::id(),
                ],
                'make_id' => [
                    'type' => Type::id(),
                ],
                'model_id' => [
                    'type' => Type::id(),
                ],
                'client_id' => [
                    'type' => Type::id(),
                ],
                'manager_id' => [
                    'type' => Type::id(),
                ],
                'schema_id' => [
                    'type' => Type::id(),
                ],
                'date_inspection_from' => [
                    'type' => Type::string(),
                    'description' => 'Format: Y-m-d, e.g. 2022-05-03',
                    'rules' => [
                        'nullable',
                        'date_format:Y-m-d'
                    ]
                ],
                'date_inspection_to' => [
                    'type' => Type::string(),
                    'description' => 'Format: Y-m-d, e.g. 2022-05-03',
                    'rules' => [
                        'nullable',
                        'date_format:Y-m-d',
                    ]
                ]
            ]
        );

        $args['sort']['defaultValue'] = ['id-desc'];

        return $args;
    }

    public function type(): Type
    {
        return VehicleType::paginate();
    }

    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): LengthAwarePaginator
    {
        return $this->service->show(
            $args,
            $fields->getRelations(),
            $fields->getSelect(),
            $this->user()
        );
    }

    abstract protected function setQueryGuard(): void;
}
