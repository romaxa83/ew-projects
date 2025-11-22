<?php

namespace App\GraphQL\Mutations\BackOffice\Dictionaries\VehicleClasses;

use App\Dto\Dictionaries\VehicleClassDto;
use App\GraphQL\InputTypes\Dictionaries\VehicleClassInputType;
use App\GraphQL\Types\Dictionaries\VehicleClassType;
use App\GraphQL\Types\NonNullType;
use App\Models\Dictionaries\VehicleClass;
use App\Permissions\Dictionaries\DictionaryUpdatePermission;
use App\Services\Dictionaries\VehicleClassService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class VehicleClassUpdateMutation extends BaseMutation
{
    public const NAME = 'vehicleClassUpdate';
    public const PERMISSION = DictionaryUpdatePermission::KEY;

    public function __construct(private VehicleClassService $service)
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return VehicleClassType::nonNullType();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'rules' => [
                    'required',
                    'int',
                    Rule::exists(VehicleClass::class, 'id')
                ]
            ],
            'vehicle_class' => [
                'type' => VehicleClassInputType::nonNullType()
            ]
        ];
    }

    /**
     * @param mixed $root
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @param SelectFields $fields
     * @return VehicleClass
     * @throws Throwable
     */
    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): VehicleClass
    {
        return makeTransaction(
            fn() => $this->service->update(
                VehicleClassDto::byArgs($args['vehicle_class']),
                VehicleClass::find($args['id'])
            )
        );
    }
}
