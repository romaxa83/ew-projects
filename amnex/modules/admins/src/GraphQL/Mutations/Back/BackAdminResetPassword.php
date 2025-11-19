<?php

namespace Wezom\Admins\GraphQL\Mutations\Back;

use Illuminate\Validation\Rules\Password;
use Wezom\Admins\Models\Admin;
use Wezom\Admins\Services\AdminService;
use Wezom\Admins\Services\AdminVerificationService;
use Wezom\Core\Exceptions\TranslatedException;
use Wezom\Core\GraphQL\BaseFieldResolver;
use Wezom\Core\GraphQL\Context;

final class BackAdminResetPassword extends BaseFieldResolver
{
    protected bool $runInTransaction = true;

    public function __construct(
        protected AdminService $adminService,
        protected AdminVerificationService $adminVerificationService
    ) {
    }

    public function resolve(Context $context): bool
    {
        $decrypt = $this->adminVerificationService->decryptTokenForEmailReset($context->getArg('token'));
        $admin = Admin::query()->active()->whereKey($decrypt['id'])->first();

        $this->check($decrypt, $admin);

        $this->adminService->changePassword($admin, $context->getArg('password'));
        $this->adminVerificationService->cleanEmailVerificationCode($admin);

        return true;
    }

    protected function check(array $data, ?Admin $admin = null): void
    {
        if (now()->parse($data['time'])->addDay()->timestamp < time()) {
            throw new TranslatedException(__('admins::exceptions.password_reset_timed_out'));
        }

        if (!$admin) {
            throw new TranslatedException(__('admins::exceptions.no_active_user_with_this_email'));
        }

        if ((int)$admin->getEmailVerificationCode() !== (int)$data['code']) {
            throw new TranslatedException(__('admins::exceptions.invalid_password_reset_link'));
        }
    }

    protected function rules(array $args = []): array
    {
        return [
            'token' => ['required', 'string'],
            'password' => ['required', 'string', Password::default()],
            'password_confirmation' => ['required', 'same:password'],
        ];
    }
}
