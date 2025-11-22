<?php

namespace App\Services\Sms;

use App\Events\SmsVerify\SendSmsCode;
use App\Exceptions\ErrorsCode;
use App\Services\Sms\Exceptions\SmsVerifyException;
use App\Models\Verify\SmsVerify;
use App\Repositories\Sms\SmsVerifyRepository;
use App\Services\Tokenizer;
use App\ValueObjects\Phone;
use App\ValueObjects\Token;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterval;

class SmsVerifyService
{
    public function __construct(
        private SmsVerifyRepository $smsVerifyRepository
    )
    {}

    public function create(Phone $phone): SmsVerify
    {
        $this->checkExistRecord($phone);

        $model = new SmsVerify();
        $model->phone = $phone;
        $model->sms_code = $this->getCode();
        $model->sms_token = $this->getSmsToken();

        $model->save();

        event(new SendSmsCode($model));

        return $model;
    }

    // действия если при запросе есть запись по данному телефону
    private function checkExistRecord(Phone $phone)
    {
        // логика описана в диаграмме /docs/diagrams/sms_verify/sms_verify.exist.puml
        if($obj = $this->smsVerifyRepository->getByPhone($phone)){
            if($obj->action_token){
                if($obj->action_token->isExpiredToNow()){
                    $obj->delete();
                } else {
                    SmsVerifyException::throwActiveActionToken();
                }
            } else {
                if($obj->sms_token->isExpiredToNow()){
                    $obj->delete();
                } else {
                    SmsVerifyException::throwActiveSmsToken();
                }
            }
        }
    }

    // проверка actionToken
    public function getAndCheckByActionToken(string $actionToken): ?SmsVerify
    {
        $obj = $this->smsVerifyRepository->findByActionToken($actionToken);

        if($obj->action_token->isExpiredToNow()){
            throw new SmsVerifyException(__('error.expired action token'), ErrorsCode::ACTION_TOKEN_EXPIRED);
        }

        return $obj;
    }

    public function generateActionToken(SmsVerify $model): SmsVerify
    {
        $model->action_token = $this->getActionToken();

        $model->save();

        return $model;
    }

    public function getSmsToken(): Token
    {
        return (new Tokenizer(new CarbonInterval(config('sms.verify.sms_token_expired'))))
            ->generate(CarbonImmutable::now());
    }

    public function getActionToken(): Token
    {
        return (new Tokenizer(new CarbonInterval(config('sms.verify.action_token_expired'))))
            ->generate(CarbonImmutable::now());
    }

    public function getCode(): string
    {
        $len = config('sms.verify.code_length');

        $min = '1';
        for ($i = 0; $i < $len - 1; $i++){
            $min .= '0';
        }
        $max = '9';
        for ($i = 0; $i < $len - 1; $i++){
            $max .= '9';
        }
        return (string)random_int((int)$min, (int)$max);
    }
}

