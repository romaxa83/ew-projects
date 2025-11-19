<?php

declare(strict_types=1);

namespace Wezom\Admins\GraphQL\Mutations\Back;

use Wezom\Admins\Models\Admin;
use Wezom\Admins\Services\AdminVerificationService;
use Wezom\Core\Exceptions\TranslatedException;
use Wezom\Core\GraphQL\BaseFieldResolver;
use Wezom\Core\GraphQL\Context;

final class BackAdminEmailVerification extends BaseFieldResolver
{
    protected bool $runInTransaction = true;

    public function __construct(protected AdminVerificationService $adminVerificationService)
    {
    }

    public function resolve(Context $context): bool
    {
        $decrypt = $this->adminVerificationService->decryptTokenForEmailReset($context->getArg('token'));
        $admin = Admin::query()->active()->find($decrypt['id']);

        $this->check($decrypt, $admin);

        $this->adminVerificationService->emailVerification($admin);

        return true;
    }

    protected function check(array $data, ?Admin $admin = null): void
    {
        if (now()->parse($data['time'])->addDay()->timestamp < time()) {
            throw new TranslatedException(__('admins::exceptions.change_email_timed_out'));
        }

        if (!$admin) {
            throw new TranslatedException(__('admins::exceptions.no_active_user_with_this_email'));
        }

        if ((int)$admin->new_email_verification_code !== (int)$data['code']) {
            throw new TranslatedException(__('admins::exceptions.invalid_change_email_link'));
        }
    }

    protected function rules(array $args = []): array
    {
        return [
            'token' => ['required', 'string'],
        ];
    }
}
