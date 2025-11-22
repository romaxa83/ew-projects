<?php

namespace Api\Users\Owner;

use App\Models\Users\User;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Truck;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class OwnerShowTest extends TestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;

    public function test_it_show_additional_fields_for_owner(): void
    {
        /** @var User $user */
        $user = $this->ownerFactory();
        factory(Truck::class)->create(['owner_id' => $user->id]);
        factory(Truck::class)->create(['owner_id' => $user->id]);
        factory(Trailer::class)->create(['owner_id' => $user->id]);

        $this->loginAsCarrierSuperAdmin();

        $response = $this->getJson(route('users.show', $user))
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        'owner_trucks' => [
                            '*' => [
                                'id',
                                'vin',
                                'unit_number',
                                'license_plate',
                                'temporary_plate',
                                'make',
                                'model',
                                'year',
                                'type',
                                'tags',
                                'driver',
                            ],
                        ],
                        'owner_trailers' => [
                            '*' => [
                                'id',
                                'vin',
                                'unit_number',
                                'license_plate',
                                'temporary_plate',
                                'make',
                                'model',
                                'year',
                                'tags',
                                'driver',
                            ],
                        ],
                    ]
                ]
            );
        $this->assertCount(2, $response['data']['owner_trucks']);
        $this->assertCount(1, $response['data']['owner_trailers']);
    }
}
