<?php

namespace App\GraphQL\Mutations\Admin\Admin;

use App\GraphQL\BaseGraphQL;
use App\Models\Admin\Admin;
use App\Repositories\Admin\AdminRepository;
use App\Services\Admin\AdminService;
use App\Services\Telegram\TelegramDev;
use GraphQL\Error\Error;

class AdminRestore extends BaseGraphQL
{
    public function __construct(
        protected AdminRepository $adminRepository,
        protected AdminService $adminService,
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
            /**  @var $admin Admin */
            $admin = $this->adminRepository->trashedFindByID($args['id']);

            $admin = $this->adminService->restore($admin);

            // @todo dev-telegram
            TelegramDev::info("Админ восстановлен - ({$args['id']})", $user->name);

            return $admin;
        } catch (\Throwable $e){
            TelegramDev::error(__FILE__, $e, $user->name, TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}
