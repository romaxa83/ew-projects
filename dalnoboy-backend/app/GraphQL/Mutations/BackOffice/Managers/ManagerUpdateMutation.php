<?php

namespace App\GraphQL\Mutations\BackOffice\Managers;

use App\Dto\Managers\ManagerDto;
use App\GraphQL\InputTypes\Managers\ManagerInputType;
use App\GraphQL\Types\Managers\ManagerType;
use App\GraphQL\Types\NonNullType;
use App\Models\Managers\Manager;
use App\Permissions\Managers\ManagerUpdatePermission;
use App\Services\Managers\ManagerService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class ManagerUpdateMutation extends BaseMutation
{
    public const NAME = 'managerUpdate';
    public const PERMISSION = ManagerUpdatePermission::KEY;

    public function __construct(private ManagerService $service)
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
                    Rule::exists(Manager::class, 'id')
                ]
            ],
            'manager' => [
                'type' => ManagerInputType::nonNullType()
            ]
        ];
    }

    public function type(): Type
    {
        return ManagerType::nonNullType();
    }

    /**
     * @param $root
     * @param array $args
     * @param $context
     * @param ResolveInfo $info
     * @param SelectFields $fields
     * @return Manager
     * @throws Throwable
     */
    public function doResolve($root, array $args, $context, ResolveInfo $info, SelectFields $fields): Manager
    {
        return makeTransaction(
            fn() => $this->service->update(
                ManagerDto::byArgs($args['manager']),
                Manager::find($args['id'])
            )
        );
    }
}
