<?php

namespace WezomCms\SmsVerify\Services;

use Carbon\CarbonImmutable;
use WezomCms\SmsVerify\Exceptions\SmsVerifyException;
use WezomCms\SmsVerify\Models\SmsVerify;
use WezomCms\SmsVerify\Repositories\SmsVerifyRepository;

class SmsVerifyService
{
    public function __construct(protected SmsVerifyRepository $smsVerifyRepository)
    {
    }

    /**
     * @param string $phone
     * @return SmsVerify
     * @throws SmsVerifyException
     */
    public function create(string $phone): SmsVerify
    {
        $this->checkExistRecord($phone);

        $model = new SmsVerify();
        $model->phone = $phone;
        $model->code = $this->generateSmsCode($this->isDevPhone($phone), $phone);
        $model->sms_token = Tokenizer::generateSmsToken(CarbonImmutable::now());

        $model->save();

        return $model;
    }

    /**
     * действия если при запросе есть запись по данному телефону
     *
     * @param string $phone
     * @throws SmsVerifyException
     */
    private function checkExistRecord(string $phone): void
    {
        if ($obj = $this->smsVerifyRepository->getByPhone($phone)) {
            if ($obj->action_token) {
                $obj->delete();
                /*if ($obj->action_token->isExpiredToNow()) {
                    $obj->delete();
                } else {
                    SmsVerifyException::throwActiveActionToken();
                }*/
            } elseif ($obj->sms_token->isExpiredToNow()) {
                $obj->delete();
            } else {
                SmsVerifyException::throwActiveSmsToken();
            }
        }
    }

    public function generateSmsCode(bool $devPhone = false, string $phone = null): string
    {
        logger("GENERATE SMS CODE", [
            'phone' => $phone,
            'pretty_phone' => prettyPhone($phone),
            'dev_phone' => config('cms.sms-verify.config.verify.constant_dev_phone.phone'),
            'code' => config('cms.sms-verify.config.verify.constant_dev_phone.code'),
        ]);
        if($phone && prettyPhone($phone) === config('cms.sms-verify.config.verify.constant_dev_phone.phone')){
            return config('cms.sms-verify.config.verify.constant_dev_phone.code');
        }
        if ($devPhone && config('cms.sms-verify.config.verify.dev.enable')) {
            return config('cms.sms-verify.config.verify.dev.code');
        }

        $len = config('cms.sms-verify.config.verify.code_length');

        $min = '1';
        for ($i = 0; $i < $len - 1; $i++) {
            $min .= '0';
        }
        $max = '9';
        for ($i = 0; $i < $len - 1; $i++) {
            $max .= '9';
        }
        return (string)random_int((int)$min, (int)$max);
    }

    private function isDevPhone(string $phone): bool
    {
        return in_array($phone, config('cms.sms-verify.config.verify.dev.phones'), true)
            || prettyPhone($phone) === config('cms.sms-verify.config.verify.constant_dev_phone.phone');
    }

    // проверка actionToken
    public function getAndCheckByActionToken(string $actionToken): ?SmsVerify
    {
        $obj = $this->smsVerifyRepository->findByActionToken($actionToken);

        if ($obj->action_token->isExpiredToNow()) {
            SmsVerifyException::throwExpiredActionToken($actionToken);
        }

        return $obj;
    }

    public function setActionToken(SmsVerify $model): SmsVerify
    {
        $model->action_token = Tokenizer::generateActionToken(CarbonImmutable::now());

        $model->save();

        return $model;
    }
}
