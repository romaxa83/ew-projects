<?php

namespace Feature\Api\Users\Driver;

use App\Models\Fueling\FuelCard;
use App\Models\Fueling\FuelCardHistory;
use App\Models\Users\DriverInfo;
use App\Models\Users\User;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Helpers\Traits\DriverFactoryHelper;
use Tests\Helpers\Traits\OrderFactoryHelper;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class DriverUnassignedFuelCardTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;
    use OrderFactoryHelper;
    use DriverFactoryHelper;
    use UserFactoryHelper;

    public function test_it_success()
    {
        /** @var User $driver */

        $dbAttributes = [
            'first_name' => 'Full',
            'last_name' => 'Name',
            'phone' => '1-541-754-3010',
            'email' => $this->faker->email,
        ];

        $driver = User::factory()->create($dbAttributes);
        $fuelCard2 = FuelCard::factory()->create();
        FuelCardHistory::factory()->for($driver)->for($fuelCard2)->create(['active' => true, 'date_assigned' => now()]);
        $attributes = [
            'fuel_card_id' => $fuelCard2->id,
        ];

        $driver->assignRole(User::DRIVER_ROLE);
        DriverInfo::factory()->create(
            ['driver_id' => $driver->id]
        );
        $this->loginAsCarrierSuperAdmin();

        $this->putJson(
            route('users.unassigned-fuel-card', $driver),
            $attributes,
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        'fuel_cards' => []
                    ],
                ]
            );
        $this->assertDatabaseHas(FuelCardHistory::TABLE_NAME, [
            'active' => false,
            'fuel_card_id' => $fuelCard2->id,
            'user_id' => $driver->id,
        ]);
    }
}
