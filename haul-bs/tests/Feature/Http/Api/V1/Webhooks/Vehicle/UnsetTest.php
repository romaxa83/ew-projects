<?php

namespace Tests\Feature\Http\Api\V1\Webhooks\Vehicle;

use App\Models\Vehicles\Truck;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builders\Companies\CompanyBuilder;
use Tests\Builders\Vehicles\TrailerBuilder;
use Tests\Builders\Vehicles\TruckBuilder;
use Tests\TestCase;

class UnsetTest extends TestCase
{
    use DatabaseTransactions;

    protected TrailerBuilder $trailerBuilder;
    protected TruckBuilder $truckBuilder;
    protected CompanyBuilder $companyBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->trailerBuilder = resolve(TrailerBuilder::class);
        $this->truckBuilder = resolve(TruckBuilder::class);
        $this->companyBuilder = resolve(CompanyBuilder::class);
    }

    /** @test */
    public function success_unset()
    {
        $company = $this->companyBuilder->create();

        /** @var $truck_1 Truck */
        $truck_1 = $this->truckBuilder->company($company)->create();
        $truck_2 = $this->truckBuilder->company($company)->create();
        $trailer_1 = $this->trailerBuilder->company($company)->create();

        $this->postJson(route('api.v1.webhooks.vehicles.unset', ['companyId' => $company->id]), [], [
            'Authorization' => config('api.webhook.token')
        ])
            ->assertJson([
                'data' => [
                    'message' => 'Unset data',
                ]
            ])
        ;

        $truck_1->refresh();
        $truck_2->refresh();
        $trailer_1->refresh();

        $this->assertNotNull($truck_1->deleted_at);
        $this->assertNotNull($truck_2->deleted_at);
        $this->assertNotNull($trailer_1->deleted_at);
    }

    /** @test */
    public function fail_empty_data()
    {
        $company = $this->companyBuilder->create();

        /** @var $truck_1 Truck */
        $truck_1 = $this->truckBuilder->company($company)->create();

        $this->postJson(route('api.v1.webhooks.vehicles.unset', ['companyId' => $company->id + 1]), [], [
            'Authorization' => config('api.webhook.token')
        ])
            ->assertJson([
                'data' => [
                    'message' => 'Unset data',
                ]
            ])
        ;

        $truck_1->refresh();

        $this->assertNull($truck_1->deleted_at);
    }

    /** @test */
    public function not_auth()
    {
        $company = $this->companyBuilder->create();

        $res = $this->postJson(route('api.v1.webhooks.vehicles.unset', ['companyId' => $company->id]))
        ;

        self::assertErrorMsg($res, "Wrong webhook auth-token", Response::HTTP_UNAUTHORIZED);
    }
}
