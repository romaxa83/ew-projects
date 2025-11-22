<?php

namespace Feature\Api\Fueling;

use App\Enums\Fueling\FuelCardProviderEnum;
use App\Enums\Fueling\FuelCardStatusEnum;
use App\Models\Fueling\FuelCard;
use App\Models\Fueling\FuelCardHistory;
use App\Models\Tags\Tag;
use App\Models\Users\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class FuelCardIndexFilterTest extends TestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;

    public function test_search()
    {
        $this->loginAsCarrierSuperAdmin();

        $card1 = FuelCard::factory()->create([
            'card' => '55555',
        ]);

        $card2 = FuelCard::factory()->create([
            'card' => '11111',
        ]);

        $card3 = FuelCard::factory()->create([
            'card' => '00000',
        ]);

        $filter = ['q' => '00000'];
        $response = $this->getJson(route('fuel-cards.index', $filter))
            ->assertOk();

        $cards = $response->json('data');
        $this->assertCount(1, $cards);
        $this->assertEquals($card3->id, $cards[0]['id']);
    }

    public function test_status_search()
    {
        $this->loginAsCarrierSuperAdmin();

        $card1 = FuelCard::factory()->create([
            'card' => '55555',
            'status' => FuelCardStatusEnum::ACTIVE
        ]);

        $card2 = FuelCard::factory()->create([
            'card' => '11111',
            'status' => FuelCardStatusEnum::INACTIVE
        ]);

        $card3 = FuelCard::factory()->create([
            'card' => '00000',
            'status' => FuelCardStatusEnum::DELETED,
            'deleted_at' => now()
        ]);

        $filter = ['status' => FuelCardStatusEnum::DELETED];
        $response = $this->getJson(route('fuel-cards.index', $filter))
            ->assertOk();

        $cards = $response->json('data');
        $this->assertCount(1, $cards);
        $this->assertEquals($card3->id, $cards[0]['id']);
    }

    public function test_provider_search()
    {
        $this->loginAsCarrierSuperAdmin();

        $card1 = FuelCard::factory()->create([
            'card' => '55555',
            'provider' => FuelCardProviderEnum::EFS
        ]);

        $card2 = FuelCard::factory()->create([
            'card' => '11111',
            'provider' => FuelCardProviderEnum::EFS
        ]);

        $card3 = FuelCard::factory()->create([
            'card' => '00000',
            'provider' => FuelCardProviderEnum::QUIKQ
        ]);

        $filter = ['provider' => FuelCardProviderEnum::QUIKQ];
        $response = $this->getJson(route('fuel-cards.index', $filter))
            ->assertOk();

        $cards = $response->json('data');
        $this->assertCount(1, $cards);
        $this->assertEquals($card3->id, $cards[0]['id']);
    }

    public function test_provider_driver()
    {
        $this->loginAsCarrierSuperAdmin();
        $card1 = FuelCard::factory()->create([
            'card' => '55555',
            'provider' => FuelCardProviderEnum::EFS
        ]);

        $user = User::factory()->driver()->create();
        FuelCardHistory::factory()->for($card1)->for($user)->create();

        $card2 = FuelCard::factory()->create([
            'card' => '11111',
            'provider' => FuelCardProviderEnum::EFS
        ]);

        $user2 = User::factory()->driver()->create();
        FuelCardHistory::factory()->for($card2)->for($user2)->create();

        $card3 = FuelCard::factory()->create([
            'card' => '00000',
            'provider' => FuelCardProviderEnum::QUIKQ
        ]);

        $user3 = User::factory()->driver()->create();
        FuelCardHistory::factory()->for($card3)->for($user3)->create();

        $filter = ['driver_id' => $user3->id];
        $response = $this->getJson(route('fuel-cards.index', $filter))
            ->assertOk();

        $cards = $response->json('data');
        $this->assertCount(1, $cards);
        $this->assertEquals($card3->id, $cards[0]['id']);
    }
}
