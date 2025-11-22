<?php

namespace App\Dto;

use App\Models\Users\User;

class UserDto
{
    private array $commonUserData;

    private array $attachments;

    private DriverDto $driverData;

    private array $tags;

    public static function byParams(array $data): self
    {
        $dto = new self();

        $dto->commonUserData = [
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'phone' => $data['phone'] ?? null,
            'phone_extension' => $data['phone_extension'] ?? null,
            'phones' => $data['phones'] ?? [],
            'email' => $data['email'],
        ];

        if (isset($data['can_check_orders'])) {
            $dto->commonUserData['can_check_orders'] = $data['can_check_orders'];
        }

        if (isset($data['owner_id'])) {
            $dto->commonUserData['owner_id'] = $data['owner_id'];
        }

        $dto->tags = $data['tags'] ?? [];

        $dto->attachments = $data[User::ATTACHMENT_FIELD_NAME] ?? [];

        $dto->driverData = DriverDto::byParams($data);

        return $dto;
    }

    public function getCommonUserData(): array
    {
        return $this->commonUserData;
    }

    public function getAttachments(): array
    {
        return $this->attachments;
    }

    public function getDriverData(): DriverDto
    {
        return $this->driverData;
    }

    public function getTags(): array
    {
        return $this->tags;
    }
}
