<?php

namespace Api\Users\Driver;

use App\Models\Vehicles\Vehicle;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class DriverTrucksHistoryTest extends TestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;

    public function test_driver_trailers_history_records(): void
    {
        $this->loginAsCarrierAdmin();
        $driverId = $this->driverFactory()->id;
        $driver2Id = $this->driverFactory()->id;


        $truck1Id = $this->create($driverId);
        $this->update($truck1Id, null);

        $truck2Id = $this->create($driverId);
        $this->update($truck2Id, $driver2Id);

        $truck3Id = $this->create($driverId);


        $response = $this->getJson(route('users.driver-trucks-history', $driverId))
            ->assertOk();
        $this->assertCount(3, $response['data']);
        $this->assertEmpty($response['data'][0]['unassigned_at']);
        $this->assertNotEmpty($response['data'][1]['unassigned_at']);
        $this->assertNotEmpty($response['data'][2]['unassigned_at']);

        $response = $this->getJson(route('users.driver-trucks-history', $driver2Id))
            ->assertOk();
        $this->assertCount(1, $response['data']);
        $this->assertEmpty($response['data'][0]['unassigned_at']);
    }

    private function create(?int $driverId = null): int
    {
        $response = $this->postJson(route('trucks.store'), [
            'vin' => $this->faker->bothify('#####????###'),
            'unit_number' => 'df763',
            'make' => 'Audi',
            'model' => 'A3',
            'year' => '2020',
            'type' => Vehicle::VEHICLE_TYPE_COUPE_2,
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

    private function update(int $truckId, ?int $driverId = null): void
    {
        $this->postJson(route('trucks.update', $truckId), [
            'vin' => $this->faker->bothify('#####????###'),
            'unit_number' => 'df763',
            'make' => 'Audi',
            'model' => 'A3',
            'year' => '2020',
            'type' => Vehicle::VEHICLE_TYPE_COUPE_2,
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
