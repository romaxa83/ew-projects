<?php

namespace App\GraphQL\Mutations\Admin\Admin;

use App\GraphQL\BaseGraphQL;
use App\Models\Admin\Admin;
use App\Repositories\Admin\AdminRepository;
use App\Services\Admin\AdminService;
use App\Services\Telegram\TelegramDev;
use GraphQL\Error\Error;

class AdminDelete extends BaseGraphQL
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
     * @return array
     */
    public function __invoke($_, array $args): array
    {
        $user = \Auth::guard(Admin::GUARD)->user();
        try {
            /**  @var $admin Admin */
            $admin = $this->adminRepository->findByID($args['id']);

            if(!$admin->isInActive()){
                throw new Error(__('error.must deactivate model before delete'));
            }

            $this->adminService->delete($admin);

            // @todo dev-telegram
            TelegramDev::info("Удален админ - ({$args['id']})", $user->name);

            return $this->successResponse(__('message.admin deleted'));
        } catch (\Throwable $e){
            TelegramDev::error(__FILE__, $e, $user->name, TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}


