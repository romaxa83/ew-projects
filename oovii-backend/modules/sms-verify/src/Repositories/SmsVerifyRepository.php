<?php

namespace WezomCms\SmsVerify\Repositories;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use WezomCms\Core\Repositories\AbstractRepository;
use WezomCms\SmsVerify\Exceptions\SmsVerifyException;
use WezomCms\SmsVerify\Models\SmsVerify;

class SmsVerifyRepository extends AbstractRepository
{
    protected function query(): Builder
    {
        return SmsVerify::query();
    }

    public function getByPhone(string $phone)
    {
        return $this->query()->where('phone', $phone)->first();
    }

    /**
     * @param string $token
     * @return SmsVerify
     * @throws SmsVerifyException
     */
    public function findBySmsToken(string $token) : SmsVerify
    {
        /** @var SmsVerify $model */
        if ($model = $this->query()->where('sms_token', $token)->first()) {
            return $model;
        }

        SmsVerifyException::throwNotFoundSmsToken($token);
    }

    /**
     * @param string $token
     * @return SmsVerify
     * @throws SmsVerifyException
     */
    public function findByActionToken(string $token) : SmsVerify
    {
        /** @var SmsVerify $model */
        if ($model = $this->query()->where('action_token', $token)->first()) {
            return $model;
        }

        SmsVerifyException::throwNotFoundActionToken($token);
    }

    public function getForRemove($days)
    {
        $now = CarbonImmutable::now()->subDays($days);

        return $this->query()
            ->where('created_at', '<', $now)
            ->get();
    }
}
