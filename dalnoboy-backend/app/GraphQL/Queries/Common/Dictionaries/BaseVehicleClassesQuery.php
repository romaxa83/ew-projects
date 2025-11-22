<?php

namespace App\GraphQL\Queries\Common\Dictionaries;

use App\GraphQL\Types\Dictionaries\VehicleClassType;
use App\GraphQL\Types\Enums\Vehicles\VehicleFormEnumType;
use App\Permissions\Dictionaries\DictionaryShowPermission;
use App\Services\Dictionaries\VehicleClassService;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Rebing\GraphQL\Support\SelectFields;

abstract class BaseVehicleClassesQuery extends BaseQuery
{
    public const NAME = 'vehicleClasses';
    public const PERMISSION = DictionaryShowPermission::KEY;

    public function __construct(private VehicleClassService $service)
    {
        $this->setQueryGuard();
    }

    abstract protected function setQueryGuard(): void;

    public function args(): array
    {
        $args = array_merge(
            $this->buildArgs(['id']),
            [
                'vehicle_form' => [
                    'type' => VehicleFormEnumType::type(),
                ],
            ]
        );

        $args['sort']['defaultValue'] = [
            'id-desc'
        ];

        return $args;
    }

    public function type(): Type
    {
        return VehicleClassType::paginate();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): LengthAwarePaginator {
        return $this->service->show(
            $args, $fields->getRelations(),
            $fields->getSelect(),
            $this->user()
        );
    }
}
