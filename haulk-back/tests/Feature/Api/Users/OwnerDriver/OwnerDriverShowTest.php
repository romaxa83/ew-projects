<?php

namespace Api\Users\OwnerDriver;

use App\Models\Users\User;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Truck;
use App\Models\Vehicles\Vehicle;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class OwnerDriverShowTest extends TestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;

    public function test_it_show_additional_fields_for_owner(): void
    {
        /** @var User $user */
        $user = $this->driverOwnerFactory();
        factory(Truck::class)->create(['owner_id' => $user->id]);
        factory(Truck::class)->create(['owner_id' => $user->id]);
        factory(Trailer::class)->create(['owner_id' => $user->id]);

        $this->loginAsCarrierSuperAdmin();

        $response = $this->getJson(route('users.show', $user))
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        'owner_trucks' => [
                            '*' => [
                                'id',
                                'vin',
                                'unit_number',
                                'license_plate',
                                'temporary_plate',
                                'make',
                                'model',
                                'year',
                                'type',
                                'tags',
                                'driver',
                            ],
                        ],
                        'owner_trailers' => [
                            '*' => [
                                'id',
                                'vin',
                                'unit_number',
                                'license_plate',
                                'temporary_plate',
                                'make',
                                'model',
                                'year',
                                'tags',
                                'driver',
                            ],
                        ],
                    ]
                ]
            );
        $this->assertCount(2, $response['data']['owner_trucks']);
        $this->assertCount(1, $response['data']['owner_trailers']);
    }

    public function test_driver_owner_vehicles_history_columns(): void
    {
        $this->loginAsCarrierAdmin();

        $user = $this->driverOwnerFactory();

        $response = $this->getJson(route('users.show', $user))
            ->assertOk();

        $this->assertFalse($response['data']['hasDriverTrucksHistory']);
        $this->assertFalse($response['data']['hasDriverTrailersHistory']);
        $this->assertFalse($response['data']['hasOwnerTrucksHistory']);
        $this->assertFalse($response['data']['hasOwnerTrailersHistory']);

        $truckId = $this->createTruck($user->id, $user->id);
        $trailerId = $this->createTrailer($user->id, $user->id);

        $response = $this->getJson(route('users.show', $user))
            ->assertOk();

        $this->assertFalse($response['data']['hasDriverTrucksHistory']);
        $this->assertFalse($response['data']['hasDriverTrailersHistory']);
        $this->assertFalse($response['data']['hasOwnerTrucksHistory']);
        $this->assertFalse($response['data']['hasOwnerTrailersHistory']);

        $this->updateTruck($truckId, $this->ownerFactory()->id, $user->id);
        $this->updateTrailer($trailerId, $user->id, $this->driverFactory()->id);

        $response = $this->getJson(route('users.show', $user))
            ->assertOk();

        $this->assertFalse($response['data']['hasDriverTrucksHistory']);
        $this->assertTrue($response['data']['hasDriverTrailersHistory']);
        $this->assertTrue($response['data']['hasOwnerTrucksHistory']);
        $this->assertFalse($response['data']['hasOwnerTrailersHistory']);
    }

    private function createTrailer(int $ownerId, int $driverId): int
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
            'owner_id' => $ownerId,
            'driver_id' => $driverId,
            'color' => 'red',
        ])
            ->assertCreated();

        return $response['data']['id'];
    }

    private function createTruck(int $ownerId, int $driverId): int
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
            'driver_id' => $driverId,
            'color' => 'red',
        ])
            ->assertCreated();

        return $response['data']['id'];
    }

    private function updateTruck(int $truckId, int $ownerId, int $driverId): void
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
            'driver_id' => $driverId,
            'color' => 'red',
        ])
            ->assertOk();
    }

    private function updateTrailer(int $truckId, int $ownerId, int $driverId): void
    {
        $this->postJson(route('trailers.update', $truckId), [
            'vin' => $this->faker->bothify('#####????###'),
            'unit_number' => 'df763',
            'make' => 'Audi',
            'model' => 'A3',
            'year' => '2020',
            'license_plate' => 'SD34343',
            'temporary_plate' => 'WEF-745',
            'notes' => 'test notes',
            'owner_id' => $ownerId,
            'driver_id' => $driverId,
            'color' => 'red',
        ])
            ->assertOk();
    }
}
