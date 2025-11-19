<?php

declare(strict_types=1);

namespace Wezom\Admins\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Hash;
use Wezom\Admins\Models\Admin;

class MatchOldPassword implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $result = Hash::check(
            $value,
            auth()->guard(Admin::GUARD)->user()?->getAuthPassword()
        );

        if (!$result) {
            $fail(__('admins::auth.admin.failed'));
        }
    }
}
