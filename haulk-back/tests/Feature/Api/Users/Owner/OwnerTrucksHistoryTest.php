<?php

namespace Api\Users\Owner;

use App\Models\Vehicles\Vehicle;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class OwnerTrucksHistoryTest extends TestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;

    public function test_owner_trucks_history_records(): void
    {
        $this->loginAsCarrierAdmin();
        $ownerId = $this->ownerFactory()->id;
        $owner2Id = $this->ownerFactory()->id;


        $truck1Id = $this->create($ownerId);
        $truck2Id = $this->create($owner2Id);
        $truck3Id = $this->create($ownerId);

        $this->update($truck1Id, $owner2Id);
        $this->update($truck2Id, $ownerId);



        $response = $this->getJson(route('users.owner-trucks-history', $ownerId))
            ->assertOk();
        $this->assertCount(3, $response['data']);
        $this->assertEmpty($response['data'][0]['unassigned_at']);
        $this->assertEmpty($response['data'][1]['unassigned_at']);
        $this->assertNotEmpty($response['data'][2]['unassigned_at']);

        $response = $this->getJson(route('users.owner-trucks-history', $owner2Id))
            ->assertOk();
        $this->assertCount(2, $response['data']);
        $this->assertEmpty($response['data'][0]['unassigned_at']);
        $this->assertNotEmpty($response['data'][1]['unassigned_at']);
    }

    private function create(int $ownerId): int
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
            'owner_id' => $ownerId,
            'driver_id' => $this->driverFactory()->id,
            'color' => 'red',
        ])
            ->assertCreated();

        return $response['data']['id'];
    }

    private function update(int $truckId, int $ownerId): void
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
            'owner_id' => $ownerId,
            'driver_id' => $this->driverFactory()->id,
            'color' => 'red',
        ])
            ->assertOk();
    }
}
