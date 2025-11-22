<?php

namespace Tests\Feature\Api\Vehicles\Trailers;

use App\Models\Users\User;
use App\Models\Vehicles\Comments\TrailerComment;
use App\Models\Vehicles\Trailer;
use Tests\Feature\Api\Vehicles\VehicleIndexTest;
use Tests\Helpers\Traits\UserFactoryHelper;

class TrailerIndexTest extends VehicleIndexTest
{
    use UserFactoryHelper;

    protected string $routeName = 'trailers.index';

    protected function loginAsPermittedUser(): User
    {
        return $this->loginAsCarrierSuperAdmin();
    }

    protected function loginAsNotPermittedUser(): User
    {
        return $this->loginAsBodyShopAdmin();
    }

    public function test_it_show_for_accountant(): void
    {
        $this->loginAsCarrierAccountant();
        $this->getJson(route($this->routeName))
            ->assertOk();
    }

    public function test_it_show_for_dispatcher(): void
    {
        $this->loginAsCarrierDispatcher();
        $this->getJson(route($this->routeName))
            ->assertOk();
    }

    public function test_comments_count(): void
    {
        $trailer = factory(Trailer::class)->create();

        factory(TrailerComment::class)->create([
            'trailer_id' => $trailer->id,
            'user_id' => $this->bsAdminFactory()->id,
        ]);
        factory(TrailerComment::class)->create([
            'trailer_id' => $trailer->id,
            'user_id' => $this->dispatcherFactory()->id,
        ]);
        factory(TrailerComment::class)->create([
            'trailer_id' => $trailer->id,
            'user_id' => $this->dispatcherFactory()->id,
        ]);

        $this->loginAsPermittedUser();

        $response = $this->getJson(route($this->routeName))
            ->assertOk();

        $this->assertEquals(2, $response['data'][0]['comments_count']);
    }
}
