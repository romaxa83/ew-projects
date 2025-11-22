<?php


namespace App\GraphQL\Mutations\Common\Drivers;


use App\Dto\Drivers\DriverDto;
use App\GraphQL\InputTypes\Drivers\DriverInputType;
use App\GraphQL\Types\Drivers\DriverType;
use App\Models\Drivers\Driver;
use App\Permissions\Drivers\DriverCreatePermission;
use App\Services\Drivers\DriverService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

abstract class BaseDriverCreateMutation extends BaseMutation
{
    public const NAME = 'driverCreate';
    public const PERMISSION = DriverCreatePermission::KEY;

    public function __construct(private DriverService $service)
    {
        $this->setMutationGuard();
    }

    abstract protected function setMutationGuard(): void;

    public function args(): array
    {
        return [
            'driver' => [
                'type' => DriverInputType::nonNullType()
            ]
        ];
    }

    public function type(): Type
    {
        return DriverType::nonNullType();
    }

    /**
     * @param mixed $root
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @param SelectFields $fields
     * @return Driver
     * @throws Throwable
     */
    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): Driver
    {
        return makeTransaction(
            fn() => $this->service->create(
                DriverDto::byArgs($args['driver']),
            )
        );
    }
}
