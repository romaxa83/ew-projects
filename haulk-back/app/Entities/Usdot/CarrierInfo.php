<?php

namespace App\Entities\Usdot;

use App\Models\Locations\State;

class CarrierInfo
{
    private int $dotNumber;

    private ?int $mcNumber;

    private string $address;

    private string $city;

    private string $state;

    private string $zip;

    private string $status;

    private string $name;

    private function __construct()
    {
    }

    public static function byFmcsaCarrierAndAuthority(array $carrier, ?array $docketNumbers): self
    {
        $carrierData = $carrier['content']['carrier'];
        $docketNumbersData = $docketNumbers['content'][0] ?? null;

        $self = new self();

        if (isset($docketNumbersData['prefix']) && $docketNumbersData['prefix'] === 'MC') {
            $self->mcNumber = $docketNumbersData['docketNumber'] ?? null;
        } else {
            $self->mcNumber = null;
        }

        $self->name = $carrierData['legalName'];
        $self->dotNumber = $carrierData['dotNumber'];
        $self->address = $carrierData['phyStreet'];
        $self->city = $carrierData['phyCity'];
        $self->state = $carrierData['phyState'];
        $self->zip = $carrierData['phyZipcode'];
        $self->status = $carrierData['statusCode'];

        return $self;
    }

    public function getMcNumber(): ?int
    {
        return $this->mcNumber;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function getZip(): string
    {
        return $this->zip;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getDotNumber(): int
    {
        return $this->dotNumber;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function toArray(): array
    {
        $state = State::whereRaw('state_short_name ILIKE ?', [$this->state])->first();

        return [
            'mc_number' => $this->mcNumber,
            'name' => $this->name,
            'usdot' => $this->dotNumber,
            'address' => $this->address,
            'city' => $this->city,
            'state_id' => $state ? $state->id : null,
            'zip' => $this->zip,
            'status' => $this->status,
        ];
    }
}
