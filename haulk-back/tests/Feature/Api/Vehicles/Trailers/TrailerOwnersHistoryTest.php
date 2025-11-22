<?php

namespace Api\Vehicles\Trailers;

use App\Enums\Format\DateTimeEnum;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class TrailerOwnersHistoryTest extends TestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;

    public function test_owner_history_records(): void
    {
        $this->loginAsCarrierAdmin();

        $ownerId = $this->ownerFactory()->id;
        $trailerId = $this->create($ownerId);
        $response = $this->getJson(route('trailers.owners-history', $trailerId))
            ->assertOk();
        $this->assertCount(1, $response['data']);

        $this->update($trailerId, $ownerId);
        $response = $this->getJson(route('trailers.owners-history', $trailerId))
            ->assertOk();
        $this->assertCount(1, $response['data']);

        $ownerId = $this->ownerFactory()->id;
        $this->update($trailerId, $ownerId);
        $response = $this->getJson(route('trailers.owners-history', $trailerId))
            ->assertOk();
        $this->assertCount(2, $response['data']);
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
            'driver_attach_at' => now()->format(DateTimeEnum::DATE_TIME_FRONT),
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
            'driver_attach_at' => now()->format(DateTimeEnum::DATE_TIME_FRONT),
            'color' => 'red',
        ])
            ->assertOk();
    }
}
