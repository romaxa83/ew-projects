<?php

namespace Tests\Feature\Api\Vehicles\Trailers;

use App\Models\Vehicles\Trailer;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TrailerSameVinTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_check_vin(): void
    {
        $this->loginAsCarrierAdmin();

        $vin = 'DHFHF734FGF';
        factory(Trailer::class)->create(['vin' => $vin]);

        $response = $this->getJson(route('trailers.same-vin', ['vin' => $vin]))
            ->assertOk();

        $this->assertCount(1, $response['data']);
    }

    public function test_it_check_vin_without_current_vehicle(): void
    {
        $this->loginAsCarrierAdmin();

        $vin = 'DHFHF734FGF';
        factory(Trailer::class)->create(['vin' => $vin]);
        $trailer = factory(Trailer::class)->create(['vin' => $vin]);

        $response = $this->getJson(route('trailers.same-vin', ['vin' => $vin, 'id' => $trailer->id]))
            ->assertOk();

        $this->assertCount(1, $response['data']);
    }
}
