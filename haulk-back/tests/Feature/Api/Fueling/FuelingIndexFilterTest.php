<?php

namespace Tests\Feature\Api\Fueling;

use App\Enums\Fueling\FuelCardStatusEnum;
use App\Enums\Fueling\FuelingSourceEnum;
use App\Enums\Fueling\FuelingStatusEnum;
use App\Models\Fueling\FuelCard;
use App\Models\Fueling\Fueling;
use App\Models\Users\DriverInfo;
use App\Models\Users\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class FuelingIndexFilterTest extends TestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;

    public function test_card_search()
    {
        $this->loginAsCarrierSuperAdmin();

        $card1 = Fueling::factory()->create(['card' => '55555',]);

        $card2 = Fueling::factory()->create(['card' => '11111',]);

        $card3 = Fueling::factory()->create(['card' => '00000',]);

        $filter = ['card' => $card3->card];
        $response = $this->getJson(route('fueling.index', $filter))
            ->assertOk();

        $cards = $response->json('data');
        $this->assertCount(1, $cards);
        $this->assertEquals($card3->id, $cards[0]['id']);
    }

    public function test_state_search()
    {
        $this->loginAsCarrierSuperAdmin();

        $card1 = Fueling::factory()->create(['state' => 'NY',]);

        $card2 = Fueling::factory()->create(['state' => 'BA',]);

        $card3 = Fueling::factory()->create(['state' => 'CD',]);

        $filter = ['state' => $card3->state];
        $response = $this->getJson(route('fueling.index', $filter))
            ->assertOk();

        $cards = $response->json('data');
        $this->assertCount(1, $cards);
        $this->assertEquals($card3->id, $cards[0]['id']);
    }

    public function test_status_search()
    {
        $this->loginAsCarrierSuperAdmin();

        $card1 = Fueling::factory()->create(['status' => FuelingStatusEnum::DUE]);

        $card2 = Fueling::factory()->create(['status' => FuelingStatusEnum::DUE]);

        $card3 = Fueling::factory()->create(['status' => FuelingStatusEnum::PAID]);

        $filter = ['status' => $card3->status];
        $response = $this->getJson(route('fueling.index', $filter))
            ->assertOk();

        $cards = $response->json('data');
        $this->assertCount(1, $cards);
        $this->assertEquals($card3->id, $cards[0]['id']);
    }

    public function test_driver_search()
    {
        $this->loginAsCarrierSuperAdmin();

        $driver = User::factory()->create();
        $driver->assignRole(User::DRIVER_ROLE);
        DriverInfo::factory()->create(
            ['driver_id' => $driver->id]
        );

        $card1 = Fueling::factory()->create(['status' => FuelingStatusEnum::DUE]);

        $card2 = Fueling::factory()->create(['status' => FuelingStatusEnum::DUE]);

        $card3 = Fueling::factory()->for($driver, 'driver')->create(['status' => FuelingStatusEnum::PAID]);

        $filter = ['driver_id' => $driver->id];
        $response = $this->getJson(route('fueling.index', $filter))->assertOk();

        $cards = $response->json('data');
        $this->assertCount(1, $cards);
        $this->assertEquals($card3->id, $cards[0]['id']);
    }

    public function test_fuel_card_search()
    {
        $this->loginAsCarrierSuperAdmin();

        $fuelCard = FuelCard::factory()->create(['status' => FuelCardStatusEnum::ACTIVE()]);

        Fueling::factory()->for($fuelCard)->create();

        Fueling::factory()->for($fuelCard)->create();

        $fuelCard2 = FuelCard::factory()->create(['status' => FuelCardStatusEnum::INACTIVE()]);

        $card3 = Fueling::factory()->for($fuelCard2)->create();

        $filter = ['fuel_card_status' => FuelCardStatusEnum::ACTIVE];
        $response = $this->getJson(route('fueling.index', $filter))->assertOk();

        $cards = $response->json('data');
        $this->assertCount(2, $cards);

        $filter = ['fuel_card_status' => FuelCardStatusEnum::INACTIVE];
        $response = $this->getJson(route('fueling.index', $filter))->assertOk();

        $cards = $response->json('data');
        $this->assertEquals($card3->id, $cards[0]['id']);
    }

    public function test_source_search()
    {
        $this->loginAsCarrierSuperAdmin();

        $card1 = Fueling::factory()->create(['source' => FuelingSourceEnum::IMPORT]);

        $card2 = Fueling::factory()->create(['source' => FuelingSourceEnum::IMPORT]);

        $card3 = Fueling::factory()->create(['source' => FuelingSourceEnum::MANUALLY]);

        $filter = ['source' => $card3->source];
        $response = $this->getJson(route('fueling.index', $filter))->assertOk();

        $cards = $response->json('data');
        $this->assertCount(1, $cards);
        $this->assertEquals($card3->id, $cards[0]['id']);
    }
}
