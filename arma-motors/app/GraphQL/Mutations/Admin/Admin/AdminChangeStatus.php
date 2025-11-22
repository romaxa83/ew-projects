<?php

namespace App\GraphQL\Mutations\Admin\Admin;

use App\GraphQL\BaseGraphQL;
use App\Models\Admin\Admin;
use App\Repositories\Admin\AdminRepository;
use App\Services\Admin\AdminService;
use App\Services\Telegram\TelegramDev;
use GraphQL\Error\Error;

class AdminChangeStatus extends BaseGraphQL
{
    public function __construct(
        protected AdminService $adminService,
        protected AdminRepository $adminRepository
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
        $sadmin = \Auth::guard(Admin::GUARD)->user();
        try {
            $admin = $this->adminRepository->findByID($args['id']);

            $admin = $this->adminService->changeStatus($admin, $args['status']);

            // @todo dev-telegram
            TelegramDev::info('Изменен статус админу', $sadmin->name);

            return $admin;
        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, $sadmin->name,TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}


