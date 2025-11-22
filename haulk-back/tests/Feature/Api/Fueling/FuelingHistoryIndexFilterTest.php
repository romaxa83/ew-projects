<?php

namespace Tests\Feature\Api\Fueling;

use App\Enums\Fueling\FuelingHistoryStatusEnum;
use App\Models\Fueling\FuelingHistory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class FuelingHistoryIndexFilterTest extends TestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;

    public function test_search()
    {
        $this->loginAsCarrierSuperAdmin();

        FuelingHistory::factory()->create(['status' => FuelingHistoryStatusEnum::SUCCESS(),]);
        $card3 = FuelingHistory::factory()->create(['status' => FuelingHistoryStatusEnum::IN_QUEUE(),]);

        $filter = ['not_completed' => true];
        $response = $this->getJson(route('fueling.history', $filter))
            ->assertOk();

        $cards = $response->json('data');
        $this->assertCount(1, $cards);
        $this->assertEquals($card3->id, $cards[0]['id']);
    }

    public function test_completed_search()
    {
        $this->loginAsCarrierSuperAdmin();

        FuelingHistory::factory()->create(['status' => FuelingHistoryStatusEnum::SUCCESS(),]);
        $card = FuelingHistory::factory()->create(['status' => FuelingHistoryStatusEnum::SUCCESS(),]);
        FuelingHistory::factory()->create(['status' => FuelingHistoryStatusEnum::IN_QUEUE(),]);

        $filter = ['not_completed' => false];
        $response = $this->getJson(route('fueling.history', $filter))
            ->assertOk();

        $cards = $response->json('data');
        $this->assertCount(2, $cards);
        $this->assertEquals($card->id, $cards[0]['id']);
    }

    public function test_status_search()
    {
        $this->loginAsCarrierSuperAdmin();

        $card = FuelingHistory::factory()->create(['status' => FuelingHistoryStatusEnum::SUCCESS(),]);
        FuelingHistory::factory()->create(['status' => FuelingHistoryStatusEnum::IN_QUEUE(),]);

        $filter = ['status' => FuelingHistoryStatusEnum::SUCCESS];
        $response = $this->getJson(route('fueling.history', $filter))
            ->assertOk();

        $cards = $response->json('data');
        $this->assertCount(1, $cards);
        $this->assertEquals($card->id, $cards[0]['id']);
    }
}
