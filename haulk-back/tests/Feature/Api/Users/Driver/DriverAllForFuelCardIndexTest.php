<?php

namespace Tests\Feature\Api\Users\Driver;

use App\Models\Fueling\FuelCard;
use App\Models\Fueling\FuelCardHistory;
use App\Models\Users\DriverInfo;
use App\Models\Users\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class DriverAllForFuelCardIndexTest extends TestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;

    public function test_it_not_show_driver_list_for_unauthorized()
    {
        $this->getJson(route('all-drivers-for-fuel-cards'))
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_it_show_driver_list_for_not_permitted()
    {
        $this->loginAsCarrierDispatcher();
        $filter = ['q' => 'test'];

        $this->getJson(route('all-drivers-for-fuel-cards', $filter))
            ->assertOk();
    }

    public function test_it_driver_index_for_permitted_user()
    {
        $this->loginAsCarrierSuperAdmin();

        $dbAttributes = [
            'first_name' => 'Full',
            'last_name' => 'Name',
            'owner_id' => null,
            'phone' => '1-541-754-3010',
            'email' => $this->faker->email,
        ];
        $driver = User::factory()->create($dbAttributes);
        $driver->assignRole(User::DRIVER_ROLE);
        DriverInfo::factory()->create(
            ['driver_id' => $driver->id]
        );
        $fuelCard = FuelCard::factory()->create();
        FuelCardHistory::factory()->for($driver)->for($fuelCard)->create(['active' => true, 'date_assigned' => now()]);
        $filter = ['q' => 'Full'];

        $result = $this->getJson(route('all-drivers-for-fuel-cards', $filter))
            ->assertJsonStructure([
                'data' => [
                    [
                        'id',
                        'first_name',
                        'last_name',
                        'role_id',
                        'fuel_cards',
                    ]

                ]
            ])
            ->assertOk();

        $this->assertCount(1, $result['data']);
    }
}
