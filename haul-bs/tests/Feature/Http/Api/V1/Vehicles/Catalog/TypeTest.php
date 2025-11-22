<?php

namespace Tests\Feature\Http\Api\V1\Vehicles\Catalog;

use App\Enums\Vehicles\VehicleType;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TypeTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function success_list()
    {
        $this->loginUserAsSuperAdmin();

        $this->getJson(route('api.v1.vehicles.types'))
            ->assertJsonStructure([
                'data' => [
                    [
                        'id',
                        'name',
                    ]
                ]
            ])
            ->assertJson([
                'data' => VehicleType::getTypesList(),
            ])
        ;
    }

    /** @test */
    public function not_auth()
    {
        $res = $this->getJson(route('api.v1.vehicles.types'));

        self::assertUnauthenticatedMessage($res);
    }
}
