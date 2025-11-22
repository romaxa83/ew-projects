<?php

namespace App\GraphQL\Mutations\BackOffice\Managers;

use App\GraphQL\Types\NonNullType;
use App\Models\Managers\Manager;
use App\Permissions\Managers\ManagerDeletePermission;
use App\Services\Managers\ManagerService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class ManagerDeleteMutation extends BaseMutation
{
    public const NAME = 'managerDelete';
    public const PERMISSION = ManagerDeletePermission::KEY;

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
                Manager::find($args['id'])
            )
        );
    }
}
