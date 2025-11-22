<?php

namespace App\Rules;

use App\Models\BaseAuthenticatable;
use Exception;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Database\Eloquent\Builder;

abstract class AbstractResetPasswordRule implements Rule
{
    private string $message = '';

    /** @throws Exception */
    public function passes($attribute, $value): bool
    {
        $data = $this->getVerificationService()->decryptEmailToken($value);

        if (now()->parse($data->time)->addDay()->timestamp < time()) {
            $this->message = __('validation.custom.reset_password.time');

            return false;
        }

        $user = $this->getQuery()->find($data->id);

        if (!$user) {
            $this->message = __('validation.custom.reset_password.user');

            return false;
        }

        if ((int)$user->getEmailVerificationCode() !== $data->code) {
            $this->message = __('validation.custom.reset_password.code');

            return false;
        }

        return true;
    }

    abstract public function getVerificationService();

    abstract public function getQuery(): Builder|BaseAuthenticatable;

    public function message(): string
    {
        return $this->message;
    }
}
