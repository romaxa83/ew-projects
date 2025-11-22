<?php

namespace App\GraphQL\Mutations\Admin\Admin;

use App\DTO\Admin\AdminEditDTO;
use App\GraphQL\BaseGraphQL;
use App\Models\Admin\Admin;
use App\Models\Permission\Role;
use App\Repositories\Admin\AdminRepository;
use App\Repositories\Permission\RoleRepository;
use App\Services\Admin\AdminService;
use App\Services\Telegram\TelegramDev;
use GraphQL\Error\Error;

class AdminEdit extends BaseGraphQL
{
    public function __construct(
        protected AdminService $adminService,
        protected AdminRepository $adminRepository,
        protected RoleRepository $roleRepository
    ){}

    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     *
     * @throws Error
     *
     * @return Admin
     */
    public function __invoke($_, array $args): Admin
    {
        $user = \Auth::guard(Admin::GUARD)->user();
        try {

            $admin = $this->adminRepository->findByID((int)$args['id']);

            $dto = AdminEditDTO::byArgs($args, $this->role($args));

            $admin = $this->adminService->edit($dto, $admin);

            // @todo dev-telegram
            TelegramDev::info("Админ ({$admin->name}) отредактирован", $user->name);

            return $admin;
        } catch (\Throwable $e){
            TelegramDev::error(__FILE__, $e, $user->name, TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }

    private function role(array $args): null|Role
    {
        $role = null;
        if(isset($args["roleId"])){
            $role = $this->roleRepository->getByID($args["roleId"]);
        }

        return $role;
    }
}

