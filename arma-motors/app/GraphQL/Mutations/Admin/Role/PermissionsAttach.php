<?php

namespace App\GraphQL\Mutations\Admin\Role;

use App\GraphQL\BaseGraphQL;
use App\Models\Admin\Admin;
use App\Models\Permission\Role;
use App\Repositories\Permission\RoleRepository;
use App\Services\Permission\RoleService;
use GraphQL\Error\Error;
use App\Services\Telegram\TelegramDev;

class PermissionsAttach extends BaseGraphQL
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
            return $this->roleService->attachPermissions(
                $this->roleRepository->getByID($args['id']),
                $args['permissions']
            );
        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, $guard->name, TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}
