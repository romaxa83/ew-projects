<?php

namespace App\GraphQL\Queries\Common\Dictionaries;

use App\GraphQL\Types\Dictionaries\VehicleTypeType;
use App\Models\Dictionaries\VehicleType;
use App\Permissions\Dictionaries\DictionaryShowPermission;
use App\Services\Dictionaries\VehicleTypeService;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Rebing\GraphQL\Support\SelectFields;

abstract class BaseVehicleTypesQuery extends BaseQuery
{
    public const NAME = 'vehicleTypes';
    public const PERMISSION = DictionaryShowPermission::KEY;

    public function __construct(private VehicleTypeService $service)
    {
        $this->setQueryGuard();
    }

    abstract protected function setQueryGuard(): void;

    public function args(): array
    {
        $args = array_merge(
            $this->buildArgs(['id']),
            [
                'vehicle_class' => [
                    'type' => Type::int()
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
        return VehicleTypeType::paginate();
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
