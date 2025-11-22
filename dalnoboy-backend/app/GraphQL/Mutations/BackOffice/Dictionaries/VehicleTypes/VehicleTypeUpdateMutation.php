<?php

namespace App\GraphQL\Mutations\BackOffice\Dictionaries\VehicleTypes;

use App\Dto\Dictionaries\VehicleTypeDto;
use App\GraphQL\InputTypes\Dictionaries\VehicleTypeInputType;
use App\GraphQL\Types\Dictionaries\VehicleTypeType;
use App\GraphQL\Types\NonNullType;
use App\Models\Dictionaries\VehicleType;
use App\Permissions\Dictionaries\DictionaryUpdatePermission;
use App\Services\Dictionaries\VehicleTypeService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class VehicleTypeUpdateMutation extends BaseMutation
{
    public const NAME = 'vehicleTypeUpdate';
    public const PERMISSION = DictionaryUpdatePermission::KEY;

    public function __construct(private VehicleTypeService $service)
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return VehicleTypeType::nonNullType();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'rules' => [
                    'required',
                    'int',
                    Rule::exists(VehicleType::class, 'id')
                ]
            ],
            'vehicle_type' => [
                'type' => VehicleTypeInputType::nonNullType()
            ]
        ];
    }

    /**
     * @param mixed $root
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @param SelectFields $fields
     * @return VehicleType
     * @throws Throwable
     */
    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): VehicleType
    {
        return makeTransaction(
            fn() => $this->service->update(
                VehicleTypeDto::byArgs($args['vehicle_type']),
                VehicleType::find($args['id'])
            )
        );
    }
}
