<?php

namespace App\GraphQL\Queries\Common\Dictionaries;

use App\GraphQL\Types\Dictionaries\VehicleModelType;
use App\Models\Dictionaries\VehicleModel;
use App\Permissions\Dictionaries\DictionaryShowPermission;
use App\Services\Dictionaries\VehicleModelService;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Rebing\GraphQL\Support\SelectFields;

abstract class BaseVehicleModelsQuery extends BaseQuery
{
    public const NAME = 'vehicleModels';
    public const PERMISSION = DictionaryShowPermission::KEY;

    public function __construct(private VehicleModelService $service)
    {
        $this->setQueryGuard();
    }

    abstract protected function setQueryGuard(): void;

    public function args(): array
    {
        $args = array_merge(
            $this->buildArgs(
                [
                    'fields' => VehicleModel::ALLOWED_SORTING_FIELDS,
                    'default_value' => [
                        'id-desc'
                    ]
                ],
                [
                    'title'
                ]
            ),
            [
                'vehicle_make' => [
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
        return VehicleModelType::paginate();
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
