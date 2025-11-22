<?php


namespace App\GraphQL\Mutations\BackOffice\Drivers;


use App\GraphQL\Types\NonNullType;
use App\Models\Drivers\Driver;
use App\Permissions\Drivers\DriverDeletePermission;
use App\Services\Drivers\DriverService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class DriverDeleteMutation extends BaseMutation
{
    public const NAME = 'driverDelete';
    public const PERMISSION = DriverDeletePermission::KEY;

    public function __construct(private DriverService $service)
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'rules' => [
                    'required',
                    'int',
                    Rule::exists(Driver::class, 'id')
                ]
            ],
        ];
    }

    public function type(): Type
    {
        return NonNullType::boolean();
    }

    /**
     * @param mixed $root
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @param SelectFields $fields
     * @return bool
     * @throws Throwable
     */
    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): bool
    {
        return makeTransaction(
            fn() => $this->service->delete(
                Driver::find($args['id'])
            )
        );
    }
}
