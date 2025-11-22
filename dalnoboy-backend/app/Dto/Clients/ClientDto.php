<?php


namespace App\Dto\Clients;


use App\Traits\Dto\HasPhonesDto;
use JetBrains\PhpStorm\ArrayShape;

class ClientDto
{
    use HasPhonesDto;

    private string $name;
    private string $contactPerson;
    private ?int $managerId;
    private ?string $edrpou;
    private ?string $inn;
    private bool $offline;
    private bool $active;
    private bool $moderated;

    public static function byArgs(
        #[ArrayShape([
            "name" => "string",
            "contact_person" => "string",
            "manager_id" => "int|null",
            "edrpou" => "string|null",
            "inn" => "string|null",
            "phones" => "array",
            "is_offline" => "bool"
        ])]
        array $args
    ): self {
        $dto = new self();

        $dto->name = $args['name'];
        $dto->contactPerson = $args['contact_person'];
        $dto->managerId = $args['manager_id'] ?? null;
        $dto->edrpou = $args['edrpou'] ?? null;
        $dto->inn = $args['inn'] ?? null;
        $dto->offline = isBackOffice() ? false : $args['is_offline'];
        $dto->moderated = isBackOffice() ? $args['is_moderated'] : false;
        $dto->active = isBackOffice() ? $args['active'] : true;

        $dto->setPhones($args['phones']);

        return $dto;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getContactPerson(): string
    {
        return $this->contactPerson;
    }

    /**
     * @return int|null
     */
    public function getManagerId(): ?int
    {
        return $this->managerId;
    }

    /**
     * @return string|null
     */
    public function getEDRPOU(): ?string
    {
        return $this->edrpou;
    }

    /**
     * @return string|null
     */
    public function getINN(): ?string
    {
        return $this->inn;
    }

    public function isOffline(): bool
    {
        return $this->offline;
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
