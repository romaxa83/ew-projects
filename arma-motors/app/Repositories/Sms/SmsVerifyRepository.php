<?php

namespace App\Repositories\Sms;

use App\Exceptions\ErrorsCode;
use App\Models\Verify\SmsVerify;
use App\Repositories\AbstractRepository;
use App\ValueObjects\Phone;
use Carbon\CarbonImmutable;

class SmsVerifyRepository extends AbstractRepository
{
    public function query()
    {
        return SmsVerify::query();
    }

    public function getByPhone(Phone $phone)
    {
        return $this->query()->where('phone', $phone)->first();
    }

    /**
     * @param string $token
     * @return SmsVerify|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    public function findBySmsToken(string $token) : SmsVerify
    {
        if($model = $this->query()->where('sms_token', $token)->first()){
            return $model;
        }

        throw new \DomainException(
            __('error.not found record by sms token', ['sms_token' => $token]),
            ErrorsCode::SMS_TOKEN_NOT_FOUND_RECORD
        );
    }

    public function findByActionToken(string $token) : SmsVerify
    {
        if($model = $this->query()->where('action_token', $token)->first()){
            return $model;
        }

        throw new \DomainException(
            __('error.not found record by action token', ['action_token' => $token]),
            ErrorsCode::ACTION_TOKEN_NOT_FOUND_RECORD
        );
    }

    public function getForRemove($days)
    {
        $now = CarbonImmutable::now()->subDays($days);

        return $this->query()
            ->where('created_at', '<', $now)
            ->get();
    }
}

