<?php

namespace App\DTO\User;

use App\ValueObjects\Email;
use App\ValueObjects\Phone;

class UserDTO
{
    private string $name;
    private Phone $phone;
    private bool $phone_verify = false;
    private Email|null $email;
    private string $password;
    private string|null $egrpoy;
    private string|null $device_id;
    private string|null $fcm_token;
    private string|null $action_token;
    private string|null $lang;

    private function __construct()
    {}

    public static function byArgs(array $args): self
    {
        $self = new self();

        $self->name = $args['name'];
        $self->phone = new Phone($args['phone']);
        $self->password = $args['password'];
        $self->email = isset($args['email']) ? new Email($args['email']) : null;
        $self->egrpoy = $args['egrpoy'] ?? null;
        $self->device_id = $args['deviceId'] ?? null;
        $self->fcm_token = $args['fcmToken'] ?? null;
        $self->action_token = $args['actionToken'] ?? null;

        return $self;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): Email|null
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getPhone(): Phone
    {
        return $this->phone;
    }

    public function getDeviceId(): string|null
    {
        return $this->device_id;
    }

    public function getEgrpoy(): string|null
    {
        return $this->egrpoy;
    }

    public function getFcmToken(): string|null
    {
        return $this->fcm_token;
    }

    public function getActionToken(): string|null
    {
        return $this->action_token;
    }

    public function getPhoneVerify(): bool
    {
        return $this->phone_verify;
    }

    public function phoneVerify(): self
    {
        $this->phone_verify = true;

        return $this;
    }

    public function hasActionToken(): bool
    {
        return null != $this->action_token;
    }
}

