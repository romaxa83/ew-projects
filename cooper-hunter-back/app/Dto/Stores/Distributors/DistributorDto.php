<?php

namespace App\Dto\Stores\Distributors;

use App\ValueObjects\Phone;

class DistributorDto
{
    private ?int $stateId;
    private bool $active;
    private string $address;
    private ?string $link;
    private ?Phone $phone = null;

    private CoordinatesDto $coordinates;

    /** @var array<DistributorTranslationDto> */
    private array $translations;

    public static function byArgs(array $args): self
    {
        $self = new self();

        $self->stateId = $args['state_id'] ?? null;
        $self->active = $args['active'];
        $self->address = $args['address'];

        if ($phone = $args['phone'] ?? null) {
            $self->phone = new Phone($phone);
        }

        $self->link = $args['link'] ?? null;

        $self->coordinates = CoordinatesDto::byArgs($args['coordinates']);

        $self->translations = DistributorTranslationDto::byTranslations($args['translations']);

        return $self;
    }

    public function getStateId(): ?int
    {
        return $this->stateId;
    }

    public function getActive(): bool
    {
        return $this->active;
    }

    public function getAddressSearchMetaphone(): string
    {
        return makeSearchSlug($this->getAddress());
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function getPhone(): ?Phone
    {
        return $this->phone;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function getCoordinates(): CoordinatesDto
    {
        return $this->coordinates;
    }

    /**
     * @return DistributorTranslationDto[]
     */
    public function getTranslations(): array
    {
        return $this->translations;
    }
}
