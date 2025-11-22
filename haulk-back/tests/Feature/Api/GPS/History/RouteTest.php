<?php

namespace Tests\Feature\Api\GPS\History;

use App\Enums\Format\DateTimeEnum;
use App\Http\Controllers\Api\Helpers\DbConnections;
use App\Models\GPS\Route;
use App\Models\Vehicles\Truck;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Saas\GPS\RouteBuilder;
use Tests\Builders\Vehicles\TrailerBuilder;
use Tests\Builders\Vehicles\TruckBuilder;
use Tests\Helpers\Traits\AdminFactory;
use Tests\Helpers\Traits\AssertErrors;
use Tests\Helpers\Traits\Permissions\PermissionFactory;
use Tests\TestCase;

class RouteTest extends TestCase
{
    use DatabaseTransactions;
    use PermissionFactory;
    use AdminFactory;
    use AssertErrors;

    protected TruckBuilder $truckBuilder;
    protected TrailerBuilder $trailerBuilder;
    protected RouteBuilder $routeBuilder;

    public array $connectionsToTransact = [
        DbConnections::DEFAULT,
        DbConnections::GPS
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->truckBuilder = resolve(TruckBuilder::class);
        $this->trailerBuilder = resolve(TrailerBuilder::class);
        $this->routeBuilder = resolve(RouteBuilder::class);

    }

    /** @test */
    public function success_get(): void
    {
        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);

        $this->loginAsCarrierSuperAdmin();

        /** @var $truck Truck */
        $truck = $this->truckBuilder->create();

        /** @var $model Route */
        $model = $this->routeBuilder
            ->truck($truck)
            ->date($date)
            ->create();

        $this->getJson(route('gps.gps-history-route', [
            'truck_id' => $truck->id,
            'date' => $date->format(DateTimeEnum::DATE),
        ]))
            ->assertJson([
                'data' => $model->data
            ])
        ;
    }
}

