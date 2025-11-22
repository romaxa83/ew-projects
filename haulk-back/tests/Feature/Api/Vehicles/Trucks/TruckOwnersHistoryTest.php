<?php

namespace Api\Vehicles\Trucks;

use App\Enums\Format\DateTimeEnum;
use App\Models\Vehicles\Vehicle;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class TruckOwnersHistoryTest extends TestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;

    public function test_owner_history_records(): void
    {
        $this->loginAsCarrierAdmin();

        $ownerId = $this->ownerFactory()->id;
        $truckId = $this->create($ownerId);
        $response = $this->getJson(route('trucks.owners-history', $truckId))
            ->assertOk();
        $this->assertCount(1, $response['data']);

        $this->update($truckId, $ownerId);
        $response = $this->getJson(route('trucks.owners-history', $truckId))
            ->assertOk();
        $this->assertCount(1, $response['data']);

        $ownerId = $this->ownerFactory()->id;
        $this->update($truckId, $ownerId);
        $response = $this->getJson(route('trucks.owners-history', $truckId))
            ->assertOk();
        $this->assertCount(2, $response['data']);
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
            'driver_attach_at' => now()->format(DateTimeEnum::DATE_TIME_FRONT),
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
            'driver_attach_at' => now()->format(DateTimeEnum::DATE_TIME_FRONT),
            'color' => 'red',
        ])
            ->assertOk();
    }
}
