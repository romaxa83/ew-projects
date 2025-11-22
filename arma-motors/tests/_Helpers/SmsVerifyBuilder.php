<?php

namespace Tests\_Helpers;

use App\Models\Verify\SmsVerify;
use App\Services\Tokenizer;
use App\ValueObjects\Phone;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterval;

class SmsVerifyBuilder
{
    private string $phone = '+38099999888877';
    private string $sms_code = '7777';
    private string $sms_token;
    private string|null $sms_token_expires = null;
    private string $sms_token_interval = 'PT5M'; // 5min
    private string $action_token;
    private string $action_token_expires;
    private string|null $action_token_interval = 'PT1H'; // 1hour
    private bool $with_action_token = false;

    public function setPhone(string $phone)
    {
        $this->phone = $phone;
        return $this;
    }

    public function getPhone()
    {
        return new Phone($this->phone);
    }

    public function setCode(string $code)
    {
        $this->sms_code = $code;
        return $this;
    }

    public function getCode()
    {
        return $this->sms_code;
    }

    public function setSmsToken(string $token)
    {
        $this->sms_token = $token;
        return $this;
    }

    public function setSmsTokenExpires(CarbonImmutable $date)
    {
        $this->sms_token_expires = $date;
        return $this;
    }

    public function setSmsTokenInterval(string $interval)
    {
        $this->sms_token_interval = $interval;
        return $this;
    }

    public function getSmsToken()
    {
        $date = $this->sms_token_expires ?? CarbonImmutable::now();

        return (new Tokenizer(new CarbonInterval($this->sms_token_interval)))
            ->generate($date);
    }

    public function getActionToken()
    {
        $date = $this->action_token_expires ?? CarbonImmutable::now();

        return (new Tokenizer(new CarbonInterval($this->action_token_interval)))
            ->generate($date);
    }

    public function withActionToken()
    {
        $this->with_action_token = true;
        return $this;
    }

    public function create()
    {
        $model = $this->save();

        return $model;
    }

    private function save()
    {
        $attr = [
            'phone' => $this->getPhone(),
            'sms_code' => $this->getCode(),
            'sms_token' => $this->getSmsToken(),
        ];

        if($this->with_action_token){
            $attr['action_token'] = $this->getActionToken();
        }

        return SmsVerify::factory()->new($attr)->create();
    }
}

