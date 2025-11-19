<?php

declare(strict_types=1);

namespace Wezom\Admins\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Hash;
use Wezom\Admins\Models\Admin;

class LoginAdmin implements ValidationRule
{
    public function __construct(
        protected array $args
    ) {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $admin = Admin::query()->where('email', $this->args['email'])->first();
        if (!$admin || !Hash::check($this->args['password'], $admin->password)) {
            $fail(__('admins::auth.admin.failed'));
        }
    }
}
