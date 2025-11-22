<?php

namespace Tests\Unit\Validators\Usdot;

use App\Repositories\Usdot\UsdotRepository;
use App\Rules\Usdot\UsdotValidator;
use Tests\Fake\Repositories\Usdot\UsdotFakeRepository;
use Tests\TestCase;

class UsdotValidatorTest extends TestCase
{
    private UsdotFakeRepository $repository;

    public function test_usdot_validated_fail(): void
    {
        $this->repository->setCarrier(null);

        $validator = new UsdotValidator();

        self::assertFalse($validator->passes('usdot', 123456));
    }

    public function test_usdot_validated_success(): void
    {
        $this->repository->setCarrier(
            [
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
            ]
        );
        $this->repository->setAuthority(
            [
                'content' => [
                    [
                        'carrierAuthority' => [
                            'docketNumber' => 123456,
                        ]
                    ]
                ],
            ]
        );

        $validator = new UsdotValidator();

        self::assertTrue($validator->passes('usdot', 123456));
    }

    protected function setUp(): void
    {
        parent::setUp();

        $usdotFakeRepository = new UsdotFakeRepository();

        $this->app->singleton(
            UsdotRepository::class,
            fn() => $usdotFakeRepository
        );

        $this->repository = $usdotFakeRepository;
    }
}
