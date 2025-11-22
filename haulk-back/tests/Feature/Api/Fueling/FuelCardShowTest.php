<?php

namespace Feature\Api\Fueling;

use App\Models\Fueling\FuelCard;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class FuelCardShowTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_not_show_for_unauthorized_users()
    {
        $card = FuelCard::factory()->create();

        $this->getJson(route('fuel-cards.show', $card))->assertUnauthorized();
    }

    public function test_it_not_show_for_not_permitted_users()
    {
        $card = FuelCard::factory()->create();

        $this->loginAsCarrierDispatcher();

        $this->getJson(route('fuel-cards.show', $card))
            ->assertForbidden();
    }

    public function test_it_show_for_permitted_users()
    {
        $card = FuelCard::factory()->create();

        $this->loginAsCarrierSuperAdmin();

        $this->getJson(route('fuel-cards.show', $card))
            ->assertOk()
            ->assertJsonStructure(['data' => [
                'id',
                'card',
                'provider',
                'status',
                'driver',
            ]]);

        $this->loginAsCarrierAdmin();

        $this->getJson(route('fuel-cards.show', $card))
            ->assertOk()
            ->assertJsonStructure(['data' => [
                'id',
                'card',
                'provider',
                'status',
                'driver',
            ]]);
    }
}
