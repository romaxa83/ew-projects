<?php

namespace Tests\Feature\Api\GPS\History;

use App\Enums\Format\DateTimeEnum;
use App\Http\Controllers\Api\Helpers\DbConnections;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Vehicles\TrailerBuilder;
use Tests\Builders\Vehicles\TruckBuilder;
use Tests\Helpers\Traits\AdminFactory;
use Tests\Helpers\Traits\AssertErrors;
use Tests\Helpers\Traits\Permissions\PermissionFactory;
use Tests\TestCase;

class CreateRouteTest extends TestCase
{
    use DatabaseTransactions;
    use PermissionFactory;
    use AdminFactory;
    use AssertErrors;

    protected TruckBuilder $truckBuilder;
    protected TrailerBuilder $trailerBuilder;

    protected array $data;

    public array $connectionsToTransact = [
        DbConnections::DEFAULT,
        DbConnections::GPS
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->truckBuilder = resolve(TruckBuilder::class);
        $this->trailerBuilder = resolve(TrailerBuilder::class);

        $this->data = [
            'date' => CarbonImmutable::now()->format(DateTimeEnum::DATE_FRONT),
            'data' => [
                [
                    'location' => [
                        'lat' => -88.999582,
                        'lng' => 42.493112,
                    ],
                    'speeding' => false,
                    'timestamp' => CarbonImmutable::now()->timestamp,
                ],
                [
                    'location' => [
                        'lat' => 88.999582,
                        'lng' => 52.493112,
                    ],
                    'speeding' => false,
                    'timestamp' => CarbonImmutable::now()->timestamp,
                ],
                [
                    'location' => [
                        'lat' => 78.999582,
                        'lng' => 52.493112,
                    ],
                    'speeding' => true,
                    'timestamp' => CarbonImmutable::now()->timestamp,
                ]
            ]
        ];

    }

    /** @test */
    public function success_create(): void
    {
        $this->loginAsCarrierSuperAdmin();

        $data = $this->data;

        $this->postJson(route('gps.gps-history-set-route'), $data)
            ->assertJson([
                'data' => [
                    [
                        'location' => [
                            'lat' => data_get($data, 'data.0.location.lat'),
                            'lng' => data_get($data, 'data.0.location.lng')
                        ],
                        'speeding' => data_get($data, 'data.0.speeding'),
                        'timestamp' => data_get($data, 'data.0.timestamp'),
                    ],
                    [
                        'location' => [
                            'lat' => data_get($data, 'data.1.location.lat'),
                            'lng' => data_get($data, 'data.1.location.lng')
                        ],
                        'speeding' => data_get($data, 'data.1.speeding'),
                        'timestamp' => data_get($data, 'data.1.timestamp'),
                    ],[
                        'location' => [
                            'lat' => data_get($data, 'data.2.location.lat'),
                            'lng' => data_get($data, 'data.2.location.lng')
                        ],
                        'speeding' => data_get($data, 'data.2.speeding'),
                        'timestamp' => data_get($data, 'data.2.timestamp'),
                    ]
                ],
            ])
        ;
    }
}


