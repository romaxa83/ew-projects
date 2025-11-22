<?php

namespace Feature\Api\Fueling;

use App\Enums\Fueling\FuelCardProviderEnum;
use App\Enums\Fueling\FuelCardStatusEnum;
use App\Models\Fueling\FuelCard;
use App\Models\Fueling\FuelCardHistory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class FuelCardHistoryIndexTest extends TestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;

    public function test_search()
    {
        $this->loginAsCarrierSuperAdmin();

        $card = FuelCard::factory()->create([
            'card' => '55555',
        ]);
        FuelCardHistory::factory()->for($card)->count(18)->create();
        FuelCardHistory::factory()->count(3)->create();
        $response = $this->getJson(route('fuel-cards.history', $card))
            ->assertOk();

        $histories = $response->json('data');
        $this->assertCount(15, $histories);
    }
}
