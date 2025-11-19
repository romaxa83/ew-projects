<?php

namespace Wezom\Admins\GraphQL\Mutations\Back;

use Exception;
use Wezom\Admins\Models\Admin;
use Wezom\Admins\Rules\AdminExistsAndActiveRule;
use Wezom\Admins\Services\AdminVerificationService;
use Wezom\Core\GraphQL\BaseFieldResolver;
use Wezom\Core\GraphQL\Context;

final class BackAdminForgotPassword extends BaseFieldResolver
{
    public const NAME = 'backAdminForgotPassword';

    public function resolve(Context $context): bool
    {
        $service = app(AdminVerificationService::class);

        try {
            $admin = Admin::query()->whereEmail($context->getArg('email'))->firstOrFail();

            $service->sendResetLink($admin);

            return true;
        } catch (Exception) {
            return false;
        }
    }

    protected function rules(array $args = []): array
    {
        return [
            'email' => ['required', 'email', new AdminExistsAndActiveRule()],
        ];
    }
}
