<?php

namespace App\Dto\Catalog\Tickets;

use App\Enums\Tickets\TicketStatusEnum;

class TicketDto
{
    private string $serialNumber;
    private ?string $guid;
    private ?string $code;
    private ?int $caseID;
    private TicketStatusEnum $status;

    private array $orderParts;
    private array $orderPartsIds;

    /** @var array<TicketTranslationDto> */
    private array $translations;

    public static function byArgs(array $args): self
    {
        $dto = new self();

        $dto->serialNumber = $args['serial_number'];
        $dto->guid = $args['guid'] ?? null;
        $dto->code = $args['code'] ?? null;
        $dto->caseID = data_get($args, 'case_id');
        $dto->status = TicketStatusEnum::fromValue($args['status']);
        $dto->translations = TicketTranslationDto::byTranslations($args['translations']);

        $dto->orderParts = $args['order_parts'] ?? [];
        $dto->orderPartsIds = $args['order_part_ids'] ?? [];

        return $dto;
    }

    public function getGuid(): ?string
    {
        return $this->guid;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function getStatus(): TicketStatusEnum
    {
        return $this->status;
    }

    /**
     * @return TicketTranslationDto[]
     */
    public function getTranslations(): array
    {
        return $this->translations;
    }

    public function getSerialNumber(): string
    {
        return $this->serialNumber;
    }

    public function getOrderParts(): array
    {
        return $this->orderParts;
    }

    public function getOrderPartsIds(): array
    {
        return $this->orderPartsIds;
    }

    public function getCaseID(): ?int
    {
        return $this->caseID;
    }
}
