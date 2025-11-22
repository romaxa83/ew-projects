<?php

namespace App\Dto\Catalog\Tickets;

use App\Dto\Orders\OrderPartDto;
use App\Enums\Tickets\TicketStatusEnum;

class TicketByTechnicianDto
{
    private array $args;

    /** @var array<OrderPartDto> */
    private array $orderParts = [];

    private ?string $comment;

    public function getTicketDto(): TicketDto
    {
        return TicketDto::byArgs(
            $this->prepareArgs()
        );
    }

    public static function byArgs(array $args): self
    {
        $dto = new self();

        $dto->args = $args;
        $dto->comment = $args['comment'] ?? null;

        foreach ($args['order_parts'] ?? [] as $part) {
            $dto->orderParts[] = OrderPartDto::byArgs($part);
        }

        return $dto;
    }

    private function prepareArgs(): array
    {
        return [
            'serial_number' => $this->args['serial_number'],
            'status' => TicketStatusEnum::NEW,
            'translations' => $this->prepareTranslations(),
        ];
    }

    private function prepareTranslations(): array
    {
        $translations = [];

        $translation = reset($this->args['translations']);

        foreach (languages() as $language) {
            $translations[] = [
                'language' => $language->slug,
                'title' => $translation['title'],
                'description' => $translation['description'],
            ];
        }

        return $translations;
    }

    /**
     * @return TicketTranslationDto[]
     */
    public function getTranslations(): array
    {
        return TicketTranslationDto::byTranslations(
            $this->prepareTranslations()
        );
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function hasOrderParts(): bool
    {
        return count($this->orderParts) > 0;
    }

    /**
     * @return OrderPartDto[]
     */
    public function getOrderParts(): array
    {
        return $this->orderParts;
    }

    public function getSyncingParts(): array
    {
        $sync = [];

        foreach ($this->orderParts as $part) {
            $sync[$part->getId()] = [
                'description' => $part->getDescription(),
                'quantity' => $part->getQuantity(),
            ];
        }

        return $sync;
    }
}
