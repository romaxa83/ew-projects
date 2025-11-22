<?php

namespace App\GraphQL\Mutations\Admin\Role;

use App\DTO\Permission\RoleDTO;
use App\GraphQL\BaseGraphQL;
use App\Models\Admin\Admin;
use App\Models\Permission\Role;
use App\Repositories\Permission\RoleRepository;
use App\Services\Permission\RoleService;
use App\Services\Telegram\TelegramDev;
use GraphQL\Error\Error;

class RoleEdit extends BaseGraphQL
{
    public function __construct(
        protected RoleService $roleService,
        protected RoleRepository $roleRepository
    ){}

    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     *
     * @throws Error
     *
     * @return Role
     */
    public function __invoke($_, array $args): Role
    {
        $guard = \Auth::guard(Admin::GUARD)->user();
        try {
            $dto = RoleDTO::byArgs($args);

            $role = $this->roleRepository->getByID($args['id']);

            $role = $this->roleService->update($dto, $role);

            // @todo dev-telegram
            TelegramDev::info("Отредактирована роль - {$role->name}", $guard->name);

            return $role;
        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, $guard->name, TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}
