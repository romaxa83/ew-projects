<?php

namespace App\GraphQL\Mutations\Admin\Admin;

use App\DTO\Admin\AdminDTO;
use App\Events\Admin\GeneratePassword;
use App\GraphQL\BaseGraphQL;
use App\Models\Admin\Admin;
use App\Repositories\Permission\RoleRepository;
use App\Services\Admin\AdminService;
use App\Services\Telegram\TelegramDev;
use GraphQL\Error\Error;

class AdminCreate extends BaseGraphQL
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
        $user = \Auth::guard(Admin::GUARD)->user();
        try {
            $role = null;
            if(isset($args["roleId"])){
                $role = $this->roleRepository->getByID($args["roleId"]);
            }

            $dto = AdminDTO::byArgs($args, $role);

            $admin = $this->adminService->create($dto);

            $admin->refresh();
            // отправляем на почту доступа
            event(new GeneratePassword($dto));

            // @todo dev-telegram
            TelegramDev::info('Создан админ', $user->name);

            return $admin;
        } catch (\Throwable $e){
            TelegramDev::error(__FILE__, $e, $user->name, TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}

