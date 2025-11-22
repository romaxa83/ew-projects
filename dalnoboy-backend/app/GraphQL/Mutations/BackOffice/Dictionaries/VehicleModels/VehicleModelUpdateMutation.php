<?php

namespace App\GraphQL\Mutations\BackOffice\Dictionaries\VehicleModels;

use App\Dto\Dictionaries\VehicleModelDto;
use App\GraphQL\InputTypes\Dictionaries\VehicleModelInputType;
use App\GraphQL\Types\Dictionaries\VehicleModelType;
use App\GraphQL\Types\NonNullType;
use App\Models\Dictionaries\VehicleModel;
use App\Permissions\Dictionaries\DictionaryUpdatePermission;
use App\Services\Dictionaries\VehicleModelService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class VehicleModelUpdateMutation extends BaseMutation
{
    public const NAME = 'vehicleModelUpdate';
    public const PERMISSION = DictionaryUpdatePermission::KEY;

    public function __construct(private VehicleModelService $service)
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return VehicleModelType::nonNullType();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'rules' => [
                    'required',
                    'int',
                    Rule::exists(VehicleModel::class, 'id')
                ]
            ],
            'vehicle_model' => [
                'type' => VehicleModelInputType::nonNullType()
            ]
        ];
    }

    /**
     * @param mixed $root
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @param SelectFields $fields
     * @return VehicleModel
     * @throws Throwable
     */
    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): VehicleModel
    {
        return makeTransaction(
            fn() => $this->service->update(
                VehicleModelDto::byArgs($args['vehicle_model']),
                VehicleModel::find($args['id'])
            )
        );
    }
}
