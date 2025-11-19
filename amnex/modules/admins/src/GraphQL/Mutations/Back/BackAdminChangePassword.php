<?php

namespace Wezom\Admins\GraphQL\Mutations\Back;

use Illuminate\Validation\Rules\Password;
use Wezom\Admins\Models\Admin;
use Wezom\Admins\Rules\MatchOldPassword;
use Wezom\Admins\Services\AdminService;
use Wezom\Core\GraphQL\BackFieldResolver;
use Wezom\Core\GraphQL\Context;

final class BackAdminChangePassword extends BackFieldResolver
{
    public const NAME = 'backAdminChangePassword';

    public function __construct(protected AdminService $adminService)
    {
    }

    public function resolve(Context $context): bool
    {
        return $this->adminService->changePassword(
            auth()->guard(Admin::GUARD)->user(),
            $context->getArg('password')
        );
    }

    protected function rules(array $args = []): array
    {
        return [
            'current' => ['required', 'string', new MatchOldPassword()],
            'password' => ['required', 'string', 'different:current', Password::default()],
            'password_confirmation' => ['required', 'same:password'],
        ];
    }
}
