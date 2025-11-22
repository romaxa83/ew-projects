<?php

namespace App\Traits\Dto;

use App\ValueObjects\Email;
use App\ValueObjects\Phone;

trait WithUserProps
{
    private Email $email;
    private ?Phone $phone;
    private string $firstName;
    private string $lastName;
    private ?string $password;
    private ?string $lang;
    private ?int $roleId;
    private ?string $smsAccessToken;
    private ?string $guid;

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function getPhone(): ?Phone
    {
        return $this->phone;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function hasPassword(): bool
    {
        return (bool)$this->password;
    }

    public function hasLang(): bool
    {
        return (bool)$this->lang;
    }

    public function getLang(): ?string
    {
        return $this->lang;
    }

    public function getGuid(): ?string
    {
        return $this->guid;
    }

    public function setLang(string $lang): self
    {
        $this->lang = $lang;

        return $this;
    }

    public function hasRoleId(): bool
    {
        return (bool)$this->roleId;
    }

    public function getRoleId(): int
    {
        return $this->roleId;
    }

    public function getSmsAccessToken(): ?string
    {
        return $this->smsAccessToken;
    }

    public function hasSmsAccessToken(): bool
    {
        return (bool)$this->smsAccessToken;
    }

    protected function setUserProps(array $args): void
    {
        $this->firstName = $args['first_name'];
        $this->lastName = $args['last_name'];
        $this->email = new Email($args['email']);
        $this->phone = isset($args['phone']) ? new Phone($args['phone']) : null;
        $this->password = $args['password'] ?? null;
        $this->lang = $args['lang'] ?? null;
        $this->roleId = $args['role_id'] ?? null;
        $this->smsAccessToken = $args['sms_access_token'] ?? null;
        $this->guid = $args['guid'] ?? null;
    }
}
