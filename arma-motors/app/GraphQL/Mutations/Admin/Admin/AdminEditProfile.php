<?php

namespace App\GraphQL\Mutations\Admin\Admin;

use App\DTO\Admin\AdminEditDTO;
use App\GraphQL\BaseGraphQL;
use App\Models\Admin\Admin;
use App\Repositories\Permission\RoleRepository;
use App\Services\Admin\AdminService;
use App\Services\Telegram\TelegramDev;
use GraphQL\Error\Error;

class AdminEditProfile extends BaseGraphQL
{
    public function __construct(
        protected AdminService $adminService,
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
        $admin = \Auth::guard(Admin::GUARD)->user();
        try {
            $dto = AdminEditDTO::byArgs($args);
            $admin = $this->adminService->edit($dto, $admin);

            // @todo dev-telegram
            TelegramDev::info("Админ отредактирован профиль", $admin->name);

            return $admin;
        } catch (\Throwable $e){
            TelegramDev::error(__FILE__, $e, $admin->name, TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}

