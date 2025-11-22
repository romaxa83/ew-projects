<?php

namespace App\Dto\Drivers;

use App\Traits\Dto\HasPhonesDto;
use App\ValueObjects\Email;

class DriverDto
{
    use HasPhonesDto;

    private ?Email $email;
    private string $firstName;
    private string $lastName;
    private ?string $secondName;
    private ?string $comment;
    private ?int $clientId;
    private bool $active;
    private bool $moderated;

    public static function byArgs(array $args): self
    {
        $dto = new self();

        $dto->firstName = $args['first_name'];
        $dto->lastName = $args['last_name'];
        $dto->secondName = data_get($args, 'second_name');
        $dto->email = !empty($args['email']) ? new Email($args['email']) : null;
        $dto->comment = data_get($args, 'comment');
        $dto->clientId = !empty($args['client_id']) ? (int)$args['client_id'] : null;
        $dto->moderated = isBackOffice() ? $args['is_moderated'] : false;
        $dto->active = isBackOffice() ? $args['active'] : true;

        $dto->setPhones($args['phones']);

        return $dto;
    }

    /**
     * @return Email|null
     */
    public function getEmail(): ?Email
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * @return string|null
     */
    public function getSecondName(): ?string
    {
        return $this->secondName;
    }

    /**
     * @return string|null
     */
    public function getComment(): ?string
    {
        return $this->comment;
    }

    /**
     * @return int|null
     */
    public function getClientId(): ?int
    {
        return $this->clientId;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @return bool
     */
    public function isModerated(): bool
    {
        return $this->moderated;
    }
}
