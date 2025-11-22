<?php

namespace App\GraphQL\Mutations\Admin\Role;

use App\GraphQL\BaseGraphQL;
use App\Models\Admin\Admin;
use App\Repositories\Permission\RoleRepository;
use App\Services\Permission\RoleService;
use App\Services\Telegram\TelegramDev;
use GraphQL\Error\Error;

class RoleDelete extends BaseGraphQL
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
     * @return array
     */
    public function __invoke($_, array $args): array
    {
        $guard = \Auth::guard(Admin::GUARD)->user();
        try {
            $role = $this->roleRepository->findByID($args['id']);

            $this->roleService->delete($role);

            // @todo dev-telegram
            TelegramDev::info("удалена роль - {$role->name}", $guard->name);

            return $this->successResponse(__('message.role deleted'));
        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, $guard->name, TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}
