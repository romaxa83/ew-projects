<?php

namespace Tests\Feature\Api\Fueling;

use App\Enums\Fueling\FuelCardAssignedTypeEnum;
use App\Models\Fueling\FuelCard;
use App\Models\Fueling\FuelCardHistory;
use App\Models\Users\DriverInfo;
use App\Models\Users\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class FuelCardUnassignedDriverTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_forbidden_to_users_create_for_not_authorized_users()
    {
        $card = FuelCard::factory()->create();

        $this->putJson(route('fuel-cards.unassigned', $card))->assertUnauthorized();
    }
    public function test_it_unassigned()
    {
        $this->loginAsCarrierSuperAdmin();

        $dbAttributes = [
            'first_name' => 'Full',
            'last_name' => 'Name',
            'phone' => '1-541-754-3010',
            'email' => $this->faker->email,
        ];

        $driver = User::factory()->create($dbAttributes);
        $driver->assignRole(User::DRIVER_ROLE);
        DriverInfo::factory()->create(
            ['driver_id' => $driver->id]
        );
        $fuelCard2 = FuelCard::factory()->create();
        FuelCardHistory::factory()->for($driver)->for($fuelCard2)->create(['active' => true, 'date_assigned' => now()]);


        $this->putJson(route('fuel-cards.unassigned', $fuelCard2))
            ->assertOk()
            ->assertJsonStructure(['data' => [
                'id',
                'card',
                'provider',
                'status',
                'driver',
            ]]);

        $this->assertDatabaseCount(FuelCardHistory::TABLE_NAME, 1);

        $this->assertDatabaseHas(FuelCardHistory::TABLE_NAME, [
            'active' => false,
            'fuel_card_id' => $fuelCard2->id,
            'user_id' => $driver->id,
        ]);
    }

    public function test_it_unassigned_empty_driver()
    {
        $this->loginAsCarrierSuperAdmin();

        $dbAttributes = [
            'first_name' => 'Full',
            'last_name' => 'Name',
            'phone' => '1-541-754-3010',
            'email' => $this->faker->email,
        ];

        $driver = User::factory()->create($dbAttributes);
        $driver->assignRole(User::DRIVER_ROLE);
        DriverInfo::factory()->create(
            ['driver_id' => $driver->id]
        );

        $fuelCard2 = FuelCard::factory()->create();

        $this->putJson(route('fuel-cards.unassigned', $fuelCard2))
            ->assertOk()
            ->assertJsonStructure(['data' => [
                'id',
                'card',
                'provider',
                'status',
                'driver',
            ]]);

        $this->assertDatabaseCount(FuelCardHistory::TABLE_NAME, 0);
    }
}
