<?php

declare(strict_types=1);

namespace Wezom\Admins\Rules;

use Closure;
use Exception;
use Illuminate\Contracts\Validation\ValidationRule;
use Wezom\Admins\Models\Admin;
use Wezom\Admins\Services\AdminVerificationService;

class AdminResetPasswordRule implements ValidationRule
{
    /** @throws Exception */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $data = app(AdminVerificationService::class)->decryptTokenForEmailReset($value);

        if (now()->parse($data['time'])->addDay()->timestamp < time()) {
            $fail(__('admins::validation.admin.custom.reset_password.time'));

            return;
        }

        $admin = Admin::query()->find($data['id']);

        if (!$admin) {
            $fail(__('admins::validation.admin.custom.reset_password.user'));

            return;
        }

        if ((int)$admin->getEmailVerificationCode() !== (int)$data['code']) {
            $fail(__('admins::validation.admin.custom.reset_password.code'));
        }
    }
}
