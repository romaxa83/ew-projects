<?php

namespace App\Repositories\Email;

use App\Exceptions\EmailVerifyException;
use App\Exceptions\ErrorsCode;
use App\Models\Verify\EmailVerify;
use App\Repositories\AbstractRepository;
use Carbon\CarbonImmutable;

class EmailVerifyRepository extends AbstractRepository
{
    public function query()
    {
        return EmailVerify::query();
    }

    public function getByToken(string $token): EmailVerify
    {
        return $this->query()->where('email_token', $token)->first();
    }

    public function findByToken(string $token) : EmailVerify
    {
        if($model = $this->query()->where('email_token', $token)->first()){
            return $model;
        }

        throw new EmailVerifyException(
            __('error.email_verify.not found record by token', ['token' => $token]),
            ErrorsCode::EMAIL_TOKEN_NOT_FOUND_RECORD
        );
    }

    public function getForRemove($days)
    {
        $now = CarbonImmutable::now()->subDays($days);

        return $this->query()
            ->where('email_token_expires', '<', $now)
            ->get();
    }
}


