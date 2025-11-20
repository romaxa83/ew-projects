<?php

namespace App\Rules\Users;

use App\Models\Admins\Admin;
use App\Models\Employees\Employee;
use App\Models\Users\User;
use App\Services\VerificationService;
use App\Traits\Auth\EmailCryptToken;
use Exception;
use Illuminate\Contracts\Validation\Rule;

class ResetPasswordRule implements Rule
{
    use EmailCryptToken;
    private string $message = '';
    private null|Admin|Employee $user;

    public function __construct(null|Admin|Employee $user = null)
    {
        $this->user = $user;
    }

    /** @throws Exception */
    public function passes($attribute, $value): bool
    {
        $data = $this->decryptEmailToken($value);

        if (now()->parse($data->time)->addDay()->timestamp < time()) {
            $this->message = __('validation.custom.reset_password.time');

            return false;
        }

        if(!$this->user){
            $this->user = User::query()->find($data->id);
        }

        if (!$this->user) {
            $this->message = __('validation.custom.reset_password.user');

            return false;
        }

        if ((int)$this->user->getEmailVerificationCode() !== $data->code) {
            $this->message = __('validation.custom.reset_password.code');

            return false;
        }

        return true;
    }

    public function message(): string
    {
        return $this->message;
    }
}
