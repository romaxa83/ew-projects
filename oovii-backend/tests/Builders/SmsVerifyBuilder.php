<?php

namespace Tests\Builders;

use WezomCms\SmsVerify\Models\SmsVerify;
use WezomCms\SmsVerify\Services\Tokenizer;
use WezomCms\SmsVerify\ValueObj\Token;

class SmsVerifyBuilder
{
    private $phone;
    private $code;
    private $sms_token;
    private $action_token;

    private array $data = [];

    public function setPhone(string $value): self
    {
        $this->phone = $value;
        $this->data['phone'] = $value;

        return $this;
    }

    public function setCode(string $value): self
    {
        $this->code = $value;
        $this->data['code'] = $value;

        return $this;
    }

    public function setSmsToken(Token $value): self
    {
        $this->sms_token = $value;
        $this->data['sms_token'] = $value;

        return $this;
    }

    public function setActionToken(Token $value): self
    {
        $this->action_token = $value;
        $this->data['action_token'] = $value;

        return $this;
    }

    public function withActionToken(): self
    {
        $this->setActionToken(Tokenizer::generateActionToken());

        return $this;
    }

    public function create(): SmsVerify
    {
        return $this->save();
    }

    private function save(): SmsVerify
    {
        return SmsVerify::factory()->new($this->data)->create();
    }
}


