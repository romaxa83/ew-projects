<?php

namespace Tests\Feature\Http\Api\V1\Vehicles\Trailer\Action;

use App\Models\Vehicles\Trailer;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Vehicles\TrailerBuilder;
use Tests\TestCase;

class SameVinTest extends TestCase
{
    use DatabaseTransactions;

    protected TrailerBuilder $trailerBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->trailerBuilder = resolve(TrailerBuilder::class);
    }

    /** @test */
    public function success_same_vin()
    {
        $this->loginUserAsSuperAdmin();

        $vin = '33TYE5678iHHG';
        /** @var $model Trailer */
        $model = $this->trailerBuilder->vin($vin)->create();
        $model_1 = $this->trailerBuilder->vin($vin)->create();
        $model_2 = $this->trailerBuilder->create();

        $this->getJson(route('api.v1.vehicles.trailers.same-vin', [
            'vin' => $vin
        ]))
            ->assertJsonStructure([
                'data' => [
                    [
                        'id',
                        'make',
                        'model',
                        'unit_number',
                    ]
                ]
            ])
            ->assertJson([
                'data' => [
                    ['id' => $model->id],
                    ['id' => $model_1->id],
                ]
            ])
            ->assertJsonCount(2, 'data')
        ;
    }

    /** @test */
    public function success_same_vin_exclude()
    {
        $this->loginUserAsSuperAdmin();

        $vin = '33TYE5678iHHG';
        /** @var $model Trailer */
        $model = $this->trailerBuilder->vin($vin)->create();
        $model_1 = $this->trailerBuilder->vin($vin)->create();
        $model_2 = $this->trailerBuilder->create();

        $this->getJson(route('api.v1.vehicles.trailers.same-vin', [
            'vin' => $vin,
            'id' => $model->id
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $model_1->id],
                ]
            ])
            ->assertJsonCount(1, 'data')
        ;
    }

    /** @test */
    public function success_same_vin_empty()
    {
        $this->loginUserAsSuperAdmin();

        $vin = '33TYE5678iHHG';

        $this->getJson(route('api.v1.vehicles.trailers.same-vin', [
            'vin' => $vin
        ]))
            ->assertJsonCount(0, 'data')
        ;
    }

    /** @test */
    public function not_auth()
    {
        $vin = '33TYE5678iHHG';

        $res = $this->getJson(route('api.v1.vehicles.trailers.same-vin', [
            'vin' => $vin
        ]));

        self::assertUnauthenticatedMessage($res);
    }
}
