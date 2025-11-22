<?php

namespace App\Dto\Settings;

class SettingsDto
{
    private string $phone;
    private string $email;

    public static function byArgs(array $args): self
    {
        $dto = new self();
        $dto->phone = $args['phone'];
        $dto->email = $args['email'];

        return $dto;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function getEmail(): string
    {
        return $this->email;
    }
}
