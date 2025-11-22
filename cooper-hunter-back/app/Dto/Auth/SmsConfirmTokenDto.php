<?php

namespace App\Dto\Auth;

class SmsConfirmTokenDto
{
    private string $code;
    private string $token;

    public static function byArgs(array $args): self
    {
        $self = new self();

        $self->code = $args['code'];
        $self->token = $args['token'];

        return $self;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getToken(): string
    {
        return $this->token;
    }
}
