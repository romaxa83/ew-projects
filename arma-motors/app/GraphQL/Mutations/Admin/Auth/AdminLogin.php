<?php

namespace App\GraphQL\Mutations\Admin\Auth;

use App\Events\Admin\AdminLogged;
use App\Exceptions\ErrorsCode;
use App\GraphQL\BaseGraphQL;
use App\Models\Admin\Admin;
use App\Repositories\Admin\AdminRepository;
use App\Services\Auth\AdminPassportService;
use App\Services\Telegram\TelegramDev;
use App\ValueObjects\Email;
use GraphQL\Error\Error;

class AdminLogin extends BaseGraphQL
{
    public function __construct(
        protected AdminRepository $adminRepository,
        protected AdminPassportService $passportService
    ){}

    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     *
     * @throws Error
     *
     * @return array
     */
    public function __invoke($_, array $args)
    {
        $guard = \Auth::guard(Admin::GUARD);
        try {
            $email = new Email($args['email']);
            $password = $args['password'];

            $admin = $this->getAndCheckAdminCredentials($email, $password);

            $tokens = arrayKeyToCamel($this->passportService->auth($admin->email, $password));

            $guard->setUser($admin);

            event(new AdminLogged($admin, request()->ip()));

            // @todo dev-telegram
            TelegramDev::info('Админ авторизован', $admin->name, TelegramDev::LEVEL_CRITICAL);

            return array_merge($tokens, ['user' => $admin,]);

        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }

    private function getAndCheckAdminCredentials(Email $email, string $password): Admin
    {
        $admin = $this->adminRepository->getByEmail($email);

        if(!$admin){
            throw new \InvalidArgumentException(__('auth.wrong_admin_login_credentials'), ErrorsCode::NOT_AUTH);
        }
        if(!password_verify($password, $admin->password)){
            throw new \InvalidArgumentException(__('auth.wrong_admin_login_credentials'), ErrorsCode::NOT_AUTH);
        }
        if($admin->isInActive()){
            throw new \DomainException(__('auth.not perm'), ErrorsCode::NOT_PERM);
        }
        if($admin->trashed()){
            throw new \DomainException(__('auth.not perm'), ErrorsCode::NOT_PERM);
        }

        return $admin;
    }
}
