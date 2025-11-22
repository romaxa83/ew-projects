<?php

namespace App\GraphQL\Queries\Common\Dictionaries;

use App\GraphQL\Types\Dictionaries\VehicleMakeType;
use App\GraphQL\Types\Enums\Vehicles\VehicleFormEnumType;
use App\Models\Dictionaries\VehicleMake;
use App\Permissions\Dictionaries\DictionaryShowPermission;
use App\Services\Dictionaries\VehicleMakeService;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Rebing\GraphQL\Support\SelectFields;

abstract class BaseVehicleMakesQuery extends BaseQuery
{
    public const NAME = 'vehicleMakes';
    public const PERMISSION = DictionaryShowPermission::KEY;

    public function __construct(private VehicleMakeService $service)
    {
        $this->setQueryGuard();
    }

    abstract protected function setQueryGuard(): void;

    public function args(): array
    {
        return array_merge(
            $this->buildArgs(
                [
                    'fields' => VehicleMake::ALLOWED_SORTING_FIELDS,
                    'default_value' => [
                        'title-asc'
                    ]
                ],
                [
                    'title'
                ]
            ),
            [
                'vehicle_form' => [
                    'type' => VehicleFormEnumType::type(),
                ],
            ]
        );
    }

    public function type(): Type
    {
        return VehicleMakeType::paginate();
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
