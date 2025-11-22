<?php

namespace Api\Users\Driver;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class DriverTrailersHistoryTest extends TestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;

    public function test_driver_trailers_history_records(): void
    {
        $this->loginAsCarrierAdmin();
        $driverId = $this->driverFactory()->id;
        $driver2Id = $this->driverFactory()->id;


        $trailer1Id = $this->create($driverId);
        $this->update($trailer1Id, null);

        $trailer2Id = $this->create($driverId);
        $this->update($trailer2Id, $driver2Id);

        $trailer3Id = $this->create($driverId);


        $response = $this->getJson(route('users.driver-trailers-history', $driverId))
            ->assertOk();
        $this->assertCount(3, $response['data']);
        $this->assertEmpty($response['data'][0]['unassigned_at']);
        $this->assertNotEmpty($response['data'][1]['unassigned_at']);
        $this->assertNotEmpty($response['data'][2]['unassigned_at']);

        $response = $this->getJson(route('users.driver-trailers-history', $driver2Id))
            ->assertOk();
        $this->assertCount(1, $response['data']);
        $this->assertEmpty($response['data'][0]['unassigned_at']);
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
            'color' => 'red',
        ])
            ->assertOk();
    }
}
