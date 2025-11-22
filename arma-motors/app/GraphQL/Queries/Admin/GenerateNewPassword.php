<?php

namespace App\GraphQL\Queries\Admin;

use App\DTO\Admin\AdminDTO;
use App\Events\Admin\GeneratePassword;
use App\GraphQL\BaseGraphQL;
use App\Models\Admin\Admin;
use App\Repositories\Admin\AdminRepository;
use App\Services\Admin\AdminService;
use App\Services\Telegram\TelegramDev;
use App\Traits\GraphqlResponse;
use GraphQL\Error\Error;

class GenerateNewPassword extends BaseGraphQL
{
    use GraphqlResponse;

    public function __construct(
        protected AdminService $adminService,
        protected AdminRepository $adminRepository
    ){}
    /**
     * Return information about current user
     *
     * @param null                 $_
     * @param array<string, mixed> $args
     *
     * @return Admin
     * @throws \GraphQL\Error\Error
     */
    public function __invoke($_, array $args): array
    {
        /** @var $sadmin Admin */
        $sadmin = \Auth::guard(Admin::GUARD)->user();
        try {

            $dto = AdminDTO::empty();
            $admin = $this->adminRepository->getByID($args['id']);
            $dto->setEmail($admin->email);

            $this->adminService->updatePassword($admin, $dto->getPassword());

            // отправляем на почту доступа
            event(new GeneratePassword($dto));

            // @todo dev-telegram
            TelegramDev::info("Админу ({$admin->name}) создан новый пароль", $sadmin->name);

            return $this->successResponse(__('message.admin.generate new password'));
        } catch (\Throwable $e){
            TelegramDev::error(__FILE__, $e, $sadmin->name,TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}
