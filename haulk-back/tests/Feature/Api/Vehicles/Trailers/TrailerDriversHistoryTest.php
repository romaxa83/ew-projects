<?php

namespace Tests\Feature\Api\Vehicles\Trailers;

use App\Enums\Format\DateTimeEnum;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class TrailerDriversHistoryTest extends TestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;

    public function test_driver_history_records(): void
    {
        $this->loginAsCarrierAdmin();

        $trailerId = $this->create(null);
        $response = $this->getJson(route('trailers.drivers-history', $trailerId))
            ->assertOk();
        $this->assertCount(0, $response['data']);

        $driverId = $this->driverFactory()->id;
        $trailerId = $this->create($driverId);

        $response = $this->getJson(route('trailers.drivers-history', $trailerId))
            ->assertOk();
        $this->assertCount(1, $response['data']);

        $this->update($trailerId, $driverId);
        $response = $this->getJson(route('trailers.drivers-history', $trailerId))
            ->assertOk();
        $this->assertCount(1, $response['data']);

        $driverId = $this->driverFactory()->id;
        $this->update($trailerId, $driverId);
        $response = $this->getJson(route('trailers.drivers-history', $trailerId))
            ->assertOk();
        $this->assertCount(2, $response['data']);
        $this->assertNotEmpty($response['data']['1']['unassigned_at']);

        $this->update($trailerId, null);
        $response = $this->getJson(route('trailers.drivers-history', $trailerId))
            ->assertOk();
        $this->assertCount(2, $response['data']);
        $this->assertNotEmpty($response['data']['0']['unassigned_at']);
    }

    private function create(?int $driverId = null): int
    {
        $response = $this->postJson(route('trailers.store'), [
            'vin' => $this->faker->bothify('#####????###'),
            'unit_number' => 'df763',
            'make' => 'Audi',
            'model' => 'A3',
            'year' => '2020',
            'license_plate' => 'SD34343',
            'temporary_plate' => 'WEF-745',
            'notes' => 'test notes',
            'owner_id' => $this->driverOwnerFactory()->id,
            'driver_id' => $driverId,
            'driver_attach_at' => now()->format(DateTimeEnum::DATE_TIME_FRONT),
            'color' => 'red',
        ])
            ->assertCreated();

        return $response['data']['id'];
    }

    private function update(int $trailerId, ?int $driverId = null): void
    {
        $this->postJson(route('trailers.update', $trailerId), [
            'vin' => $this->faker->bothify('#####????###'),
            'unit_number' => 'df763',
            'make' => 'Audi',
            'model' => 'A3',
            'year' => '2020',
            'license_plate' => 'SD34343',
            'temporary_plate' => 'WEF-745',
            'notes' => 'test notes',
            'owner_id' => $this->driverOwnerFactory()->id,
            'driver_id' => $driverId,
            'driver_attach_at' => now()->format(DateTimeEnum::DATE_TIME_FRONT),
            'color' => 'red',
        ])
            ->assertOk();
    }
}
