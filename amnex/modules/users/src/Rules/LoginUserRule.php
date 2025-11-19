<?php

namespace Wezom\Users\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Hash;
use Wezom\Users\Models\User;

class LoginUserRule implements ValidationRule
{
    public function __construct(
        protected array $args
    ) {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$this->passes()) {
            $fail($this->message());
        }
    }

    private function passes(): bool
    {
        if (!$user = User::findByEmail($this->args['email'])) {
            return false;
        }

        return Hash::check($this->args['password'], $user->getPassword());
    }

    public function message(): string
    {
        return __('users::validation.site.custom.credentials.invalid');
    }
}
