<?php

namespace App\GraphQL\Mutations\BackOffice\Permission;

use App\Entities\Messages\ResponseMessageEntity;
use App\Exceptions\Permissions\RoleForOwnerException;
use App\GraphQL\Types\Messages\ResponseMessageType;
use App\Models\Permissions\Role;
use App\Permissions\Roles\RoleUpdatePermission;
use App\Rules\ExistsRules\UserRoleExistsRule;
use Core\GraphQL\Mutations\BaseMutation;
use Core\Services\Permissions\RoleService;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class EmployeeRoleSetDefaultForOwnerMutation extends BaseMutation
{
    public const NAME = 'employeeRoleSetDefaultForOwner';
    public const PERMISSION = RoleUpdatePermission::KEY;
    public const DESCRIPTION = 'Устанавливает роль для пользователей как основную для владельца компании.';

    public function __construct(private RoleService $service)
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return ResponseMessageType::nonNullType();
    }

    public function args(): array
    {
        return [
            'id' => Type::id(),
        ];
    }

    /**
     * @throws Throwable
     */
    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): ResponseMessageEntity {
        $role = Role::query()
            ->forUsers()
            ->findOrFail($args['id']);

        try {
            make_transaction(fn() => $this->service->setAsDefaultForOwner($role));

            return ResponseMessageEntity::success(__('messages.roles.set-as-default-for-owner'));
        } catch (RoleForOwnerException) {
            return ResponseMessageEntity::warning(__('messages.roles.cant-be-toggled'));
        } catch (Throwable $e) {
            logger($e);

            return ResponseMessageEntity::fail(__('exceptions.default'));
        }
    }

    protected function rules(array $args = []): array
    {
        return [
            'id' => ['required', 'integer', new UserRoleExistsRule()]
        ];
    }
}
