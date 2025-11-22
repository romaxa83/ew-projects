<?php

namespace Tests\Feature\Api\Fueling;

use App\Enums\Fueling\FuelCardProviderEnum;
use App\Enums\Fueling\FuelingHistoryStatusEnum;
use App\Models\Fueling\FuelingHistory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class FuelingHistoryActiveTest extends TestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;

    public function test_get_active()
    {
        $this->loginAsCarrierSuperAdmin();

        FuelingHistory::factory()->create(['status' => FuelingHistoryStatusEnum::SUCCESS()]);
        $card3 = FuelingHistory::factory()->create([
            'status' => FuelingHistoryStatusEnum::IN_QUEUE(),
            'provider' => FuelCardProviderEnum::EFS
        ]);

        $response = $this->getJson(route('fueling.active-import'))
            ->assertOk();

        $history = $response->json('data');
        $this->assertEquals($card3->id, $history['id']);
    }

    public function test_get_no_history()
    {
        $this->loginAsCarrierSuperAdmin();

        FuelingHistory::factory()->create(['status' => FuelingHistoryStatusEnum::SUCCESS()]);
        FuelingHistory::factory()->create([
            'status' => FuelingHistoryStatusEnum::SUCCESS(),
            'provider' => FuelCardProviderEnum::EFS
        ]);

        $this->getJson(route('fueling.active-import'))
            ->assertStatus(Response::HTTP_NO_CONTENT);
    }
}
