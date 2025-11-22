<?php

namespace Tests\Feature\Api\Users\Driver;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use PHPUnit\Framework\Assert;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class DriverIndexTest extends TestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;

    public function test_it_not_show_driver_list_for_unauthorized()
    {

        $this->getJson(route('users.index'))
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_it_show_driver_list_for_not_permitted()
    {
        $this->loginAsCarrierDispatcher();

        $this->getJson(route('users.index'))
            ->assertOk();
    }

    public function test_it_driver_index_for_permitted_user()
    {
        $this->loginAsCarrierSuperAdmin();

        $this->getJson(route('users.index'))
            ->assertOk()
            ->assertJsonStructure(['data', 'links', 'meta',]);
    }
}
