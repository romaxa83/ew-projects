<?php

namespace Api\Users\Owner;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class OwnerTrailersHistoryTest extends TestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;

    public function test_owner_trailers_history_records(): void
    {
        $this->loginAsCarrierAdmin();
        $ownerId = $this->ownerFactory()->id;
        $owner2Id = $this->ownerFactory()->id;


        $trailer1Id = $this->create($ownerId);
        $trailer2Id = $this->create($owner2Id);
        $trailer3Id = $this->create($ownerId);

        $this->update($trailer1Id, $owner2Id);
        $this->update($trailer2Id, $ownerId);



        $response = $this->getJson(route('users.owner-trailers-history', $ownerId))
            ->assertOk();
        $this->assertCount(3, $response['data']);
        $this->assertEmpty($response['data'][0]['unassigned_at']);
        $this->assertEmpty($response['data'][1]['unassigned_at']);
        $this->assertNotEmpty($response['data'][2]['unassigned_at']);

        $response = $this->getJson(route('users.owner-trailers-history', $owner2Id))
            ->assertOk();
        $this->assertCount(2, $response['data']);
        $this->assertEmpty($response['data'][0]['unassigned_at']);
        $this->assertNotEmpty($response['data'][1]['unassigned_at']);
    }

    private function create(int $ownerId): int
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
            'driver_id' => $this->driverFactory()->id,
            'color' => 'red',
        ])
            ->assertCreated();

        return $response['data']['id'];
    }

    private function update(int $trailerId, int $ownerId): void
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
            'owner_id' => $ownerId,
            'driver_id' => $this->driverFactory()->id,
            'color' => 'red',
        ])
            ->assertOk();
    }
}
