<?php

namespace App\GraphQL\Mutations\BackOffice\Dictionaries\VehicleClasses;

use App\Dto\Dictionaries\VehicleClassDto;
use App\GraphQL\InputTypes\Dictionaries\VehicleClassInputType;
use App\GraphQL\Types\Dictionaries\VehicleClassType;
use App\Models\Dictionaries\VehicleClass;
use App\Permissions\Dictionaries\DictionaryCreatePermission;
use App\Services\Dictionaries\VehicleClassService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class VehicleClassCreateMutation extends BaseMutation
{
    public const NAME = 'vehicleClassCreate';
    public const PERMISSION = DictionaryCreatePermission::KEY;

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
            fn() => $this->service->create(
                VehicleClassDto::byArgs($args['vehicle_class'])
            )
        );
    }
}
