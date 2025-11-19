<?php

declare(strict_types=1);

namespace Wezom\Admins\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Wezom\Admins\Models\Admin;

class AdminExistsAndActiveRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $admin = Admin::query()
            ->whereEmail($value)
            ->first();
        if (!$admin) {
            $fail(__('admins::validation.admin.admin_not_exists'));
        }

        if ($admin && !$admin->active) {
            $fail(__('admins::validation.admin.this_admin_has_been_deactivated'));
        }
    }
}
