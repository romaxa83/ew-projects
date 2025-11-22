<?php

namespace Tests\_Helpers;

use App\Models\User\User;
use App\Models\Verify\EmailVerify;
use App\Services\Tokenizer;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterval;

class EmailVerifyBuilder
{
    private User $user;
    private $email_token;
    private string|null $email_token_expires = null;
    private string $email_token_interval = 'PT5M'; // 5min
    private bool $verify = false;

    public function setUser(User $user)
    {
        $this->user = $user;

        return $this;
    }

    public function setEmailToken(string $token)
    {
        $this->email_token = $token;
        return $this;
    }

    public function setEmailTokenExpires(CarbonImmutable $date)
    {
        $this->email_token_expires = $date;
        return $this;
    }

    public function setEmailTokenInterval(string $interval)
    {
        $this->email_token_interval = $interval;
        return $this;
    }

    public function verify(): self
    {
        $this->verify = true;
        return $this;
    }

    public function getEmailToken()
    {
        return $this->email_token;
    }

    protected function createToken()
    {
        $date = $this->email_token_expires ?? CarbonImmutable::now();

        $this->email_token = (new Tokenizer(new CarbonInterval($this->email_token_interval)))
            ->generate($date);
    }

    public function create()
    {
        $this->createToken();

        $model = $this->save();

        return $model;
    }

    private function save()
    {
        $attr = [
            'entity_type' => $this->user::class,
            'entity_id' => $this->user->id,
            'email_token' => $this->getEmailToken(),
            'verify' => $this->verify,
        ];

        return EmailVerify::factory()->new($attr)->create();
    }
}


