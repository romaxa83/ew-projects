<?php

namespace App\GraphQL\Mutations\BackOffice\Dictionaries\VehicleMakes;

use App\Dto\Dictionaries\VehicleMakeDto;
use App\GraphQL\InputTypes\Dictionaries\VehicleMakeInputType;
use App\GraphQL\Types\Dictionaries\VehicleMakeType;
use App\GraphQL\Types\NonNullType;
use App\Models\Dictionaries\VehicleMake;
use App\Permissions\Dictionaries\DictionaryUpdatePermission;
use App\Services\Dictionaries\VehicleMakeService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class VehicleMakeUpdateMutation extends BaseMutation
{
    public const NAME = 'vehicleMakeUpdate';
    public const PERMISSION = DictionaryUpdatePermission::KEY;

    public function __construct(private VehicleMakeService $service)
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return VehicleMakeType::nonNullType();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'rules' => [
                    'required',
                    'int',
                    Rule::exists(VehicleMake::class, 'id')
                ]
            ],
            'vehicle_make' => [
                'type' => VehicleMakeInputType::nonNullType()
            ]
        ];
    }

    /**
     * @param mixed $root
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @param SelectFields $fields
     * @return VehicleMake
     * @throws Throwable
     */
    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): VehicleMake
    {
        return makeTransaction(
            fn() => $this->service->update(
                VehicleMakeDto::byArgs($args['vehicle_make']),
                VehicleMake::find($args['id'])
            )
        );
    }
}
