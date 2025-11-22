<?php

namespace Tests\Fake\Repositories\Usdot;

use App\Repositories\Usdot\UsdotRepository;

class UsdotFakeRepository implements UsdotRepository
{
    private ?array $carrier = [
        'content' => [
            'carrier' => [
                'legalName' => 'LEGAL NAME',
                'dotNumber' => 123456,
                'phyStreet' => 'STREET 123',
                'phyCity' => 'VEGAS',
                'phyState' => 'EA',
                'phyZipcode' => 12345,
                'statusCode' => 'A',
            ]
        ],
    ];

    private ?array $authority = [
        'content' => [
            [
                'carrierAuthority' => [
                    'docketNumber' => 123456,
                ]
            ]
        ],
    ];

    private ?array $docketNumbers = [
        'content' => [
            [
                'docketNumber' => 123456,
                'docketNumberId' => 992547,
                'dotNumber' => 123456,
                'prefix' => "MC",
            ]
        ],
    ];

    public function fetchCarrierByUsdot(int $usdot): ?array
    {
        return $this->carrier;
    }

    public function fetchAuthorityByUsdot(int $usdot): ?array
    {
        return $this->authority;
    }

    public function fetchDocketNumbersByUsdot(int $usdot): ?array
    {
        return $this->docketNumbers;
    }

    public function setCarrier(?array $carrier): self
    {
        $this->carrier = $carrier;

        return $this;
    }

    public function setAuthority(?array $authority): self
    {
        $this->authority = $authority;

        return $this;
    }
}
